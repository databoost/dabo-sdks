# frozen_string_literal: true
# © 2026 Bradley Giesbrecht, © 2026 DataBoost™, LLC, © 2026 DataBoost™ Inc. All Rights Reserved.

require 'json'
require 'net/http'
require 'uri'

module Databoost
  module Sro
    # Thin HTTP client. Method names mirror the PHP Client / OpenAPI.
    class Client
      SequenceRow = Struct.new(:id, :sequence, :sticky, keyword_init: true)

      def self.row_from_api(row)
        SequenceRow.new(
          id: row['id'].to_s,
          sequence: row['sequence'].to_i,
          sticky: row['sticky'] == true
        )
      end

      def initialize(base_url:, api_token:, tenant_id:)
        @base_url = base_url.sub(%r{/\z}, '')
        @api_token = api_token
        @tenant_id = tenant_id
      end

      def health
        data = json_request(:get, '/health', nil, auth: false)
        { 'status' => data['status'].to_s }
      end

      # items: array of hashes with :id / 'id', optional :sort_key, :sort_data_type
      def sync_natural(list_id, items, expected_version: nil)
        payload = {
          items: items.map { |item| normalize_sync_item(item) }
        }
        payload[:expected_version] = expected_version unless expected_version.nil?
        ranking_request(:post, list_path(list_id, 'syncNatural'), payload)
      end

      def list(list_id)
        ranking_request(:get, list_path(list_id), nil)
      end

      def jump(list_id, item_id, to_sequence, expected_version: nil)
        payload = { item_id: item_id, to_sequence: to_sequence }
        payload[:expected_version] = expected_version unless expected_version.nil?
        ranking_request(:post, list_path(list_id, 'jump'), payload)
      end

      def reorder(list_id, item_id, after_item_id, before_item_id: nil, expected_version: nil)
        payload = { item_id: item_id }
        if before_item_id
          payload[:before_item_id] = before_item_id
        else
          payload[:after_item_id] = after_item_id
        end
        payload[:expected_version] = expected_version unless expected_version.nil?
        ranking_request(:post, list_path(list_id, 'reorder'), payload)
      end

      def remove(list_id, item_id, expected_version: nil)
        payload = { item_id: item_id }
        payload[:expected_version] = expected_version unless expected_version.nil?
        ranking_request(:post, list_path(list_id, 'remove'), payload)
      end

      def reset_sticky(list_id, item_id, expected_version: nil)
        payload = { item_id: item_id }
        payload[:expected_version] = expected_version unless expected_version.nil?
        ranking_request(:post, list_path(list_id, 'resetSticky'), payload)
      end

      def reset_stickies(list_id, expected_version: nil)
        payload = expected_version.nil? ? nil : { expected_version: expected_version }
        ranking_request(:post, list_path(list_id, 'resetStickies'), payload)
      end

      private

      def normalize_sync_item(item)
        h = item.transform_keys(&:to_sym)
        {
          id: h.fetch(:id).to_s,
          sort_key: h[:sort_key],
          sort_data_type: h[:sort_data_type]
        }
      end

      def list_path(list_id, action = nil)
        path = "/v1/tenants/#{URI.encode_www_form_component(@tenant_id)}/lists/#{URI.encode_www_form_component(list_id)}"
        action ? "#{path}/#{action}" : path
      end

      def ranking_request(method, path, body)
        data = json_request(method, path, body, auth: true)
        (data['items'] || []).map { |row| self.class.row_from_api(row) }
      end

      def json_request(method, path, body, auth:)
        uri = URI.parse("#{@base_url}#{path}")
        http = Net::HTTP.new(uri.host, uri.port)
        http.use_ssl = uri.scheme == 'https'

        req = http_class(method).new(uri)
        req['Accept'] = 'application/json'
        if auth
          req['Authorization'] = "Bearer #{@api_token}"
          req['X-Tenant-Id'] = @tenant_id
        end
        if body
          req['Content-Type'] = 'application/json'
          req.body = JSON.generate(body)
        end

        res = http.request(req)
        data = JSON.parse(res.body)
        if !res.is_a?(Net::HTTPSuccess)
          message = data.dig('error', 'message') || "SRO HTTP #{res.code}"
          raise Error, message
        end
        data
      end

      def http_class(method)
        case method
        when :get then Net::HTTP::Get
        when :post then Net::HTTP::Post
        when :put then Net::HTTP::Put
        when :patch then Net::HTTP::Patch
        when :delete then Net::HTTP::Delete
        else
          raise Error, "Unsupported method #{method}"
        end
      end
    end
  end
end
