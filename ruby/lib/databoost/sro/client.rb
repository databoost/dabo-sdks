# frozen_string_literal: true

require 'json'
require 'net/http'
require 'uri'

module Databoost
  module Sro
    # Thin HTTP client. Method names mirror the PHP StatefulSroClient / OpenAPI.
    class Client
      SequenceRow = Struct.new(:id, :sequence, keyword_init: true)

      def initialize(base_url:, api_token:, tenant_id:)
        @base_url = base_url.sub(%r{/\z}, '')
        @api_token = api_token
        @tenant_id = tenant_id
      end

      # items: array of hashes with :id / 'id', optional :sort_key, :sort_data_type
      def sync_natural(list_id, items)
        payload = {
          items: items.map { |item| normalize_sync_item(item) }
        }
        request(:post, list_path(list_id, 'syncNatural'), payload)
      end

      def list(list_id)
        request(:get, list_path(list_id), nil)
      end

      def jump(list_id, item_id, to_sequence)
        request(:post, list_path(list_id, 'jump'), {
                  item_id: item_id,
                  to_sequence: to_sequence
                })
      end

      def reorder(list_id, item_id, after_item_id)
        request(:post, list_path(list_id, 'reorder'), {
                  item_id: item_id,
                  after_item_id: after_item_id
                })
      end

      def remove(list_id, item_id)
        request(:post, list_path(list_id, 'remove'), { item_id: item_id })
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

      def request(method, path, body)
        uri = URI.parse("#{@base_url}#{path}")
        http = Net::HTTP.new(uri.host, uri.port)
        http.use_ssl = uri.scheme == 'https'

        req =
          case method
          when :get then Net::HTTP::Get.new(uri)
          when :post then Net::HTTP::Post.new(uri)
          else
            raise Error, "Unsupported method #{method}"
          end

        req['Authorization'] = "Bearer #{@api_token}"
        req['X-Tenant-Id'] = @tenant_id
        req['Accept'] = 'application/json'
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

        (data['items'] || []).map do |row|
          SequenceRow.new(id: row['id'].to_s, sequence: row['sequence'].to_i)
        end
      end
    end
  end
end
