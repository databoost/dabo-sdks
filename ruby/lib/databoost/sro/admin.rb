# frozen_string_literal: true
# © 2026 Bradley Giesbrecht, © 2026 DataBoost™, LLC, © 2026 DataBoost™ Inc. All Rights Reserved.

require 'json'
require 'net/http'
require 'uri'

module Databoost
  module Sro
    # SRO admin HTTP client (SRO_ADMIN_TOKEN). No X-Tenant-Id.
    class AdminClient
      def initialize(base_url:, admin_token:)
        @base_url = base_url.sub(%r{/\z}, '')
        @admin_token = admin_token
      end

      def health
        data = json_request(:get, '/health', nil, auth: false)
        { 'status' => data['status'].to_s }
      end

      def list_tenants
        json_request(:get, '/admin/v1/tenants', nil, auth: true)
      end

      def reconcile_tenants(tenant_ids, dry_run: false, allow_empty: false)
        json_request(:post, '/admin/v1/tenants/reconcile', {
                       tenant_ids: tenant_ids,
                       dry_run: dry_run,
                       allow_empty: allow_empty
                     }, auth: true)
      end

      def provision_tenant(tenant_id, body)
        json_request(:put, "/admin/v1/tenants/#{URI.encode_www_form_component(tenant_id)}", body, auth: true)
      end

      def update_tenant(tenant_id, body)
        json_request(:patch, "/admin/v1/tenants/#{URI.encode_www_form_component(tenant_id)}", body, auth: true)
      end

      def delete_tenant(tenant_id)
        json_request(:delete, "/admin/v1/tenants/#{URI.encode_www_form_component(tenant_id)}", nil, auth: true)
      end

      def regenerate_token(tenant_id, token_label: nil)
        body = token_label.nil? ? nil : { token_label: token_label }
        json_request(:post, "/admin/v1/tenants/#{URI.encode_www_form_component(tenant_id)}/token", body, auth: true)
      end

      def revoke_token(tenant_id)
        json_request(:delete, "/admin/v1/tenants/#{URI.encode_www_form_component(tenant_id)}/token", nil, auth: true)
      end

      private

      def json_request(method, path, body, auth:)
        uri = URI.parse("#{@base_url}#{path}")
        http = Net::HTTP.new(uri.host, uri.port)
        http.use_ssl = uri.scheme == 'https'
        req = http_class(method).new(uri)
        req['Accept'] = 'application/json'
        req['Authorization'] = "Bearer #{@admin_token}" if auth
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
