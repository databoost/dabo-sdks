# frozen_string_literal: true
# © 2026 Bradley Giesbrecht, © 2026 DataBoost™, LLC, © 2026 DataBoost™ Inc. All Rights Reserved.

require 'minitest/autorun'
require_relative '../lib/databoost/sro'

class ClientRowTest < Minitest::Test
  def test_maps_sticky_true
    row = Databoost::Sro::Client.row_from_api(
      'id' => 'job-1',
      'sequence' => 3,
      'sticky' => true
    )
    assert_equal 'job-1', row.id
    assert_equal 3, row.sequence
    assert_equal true, row.sticky
  end

  def test_missing_sticky_is_false
    row = Databoost::Sro::Client.row_from_api('id' => 'a', 'sequence' => 1)
    assert_equal false, row.sticky
  end

  def test_does_not_surface_major_minor
    row = Databoost::Sro::Client.row_from_api(
      'id' => 'b',
      'sequence' => 2,
      'sticky' => false,
      'major_minor' => '5.1'
    )
    refute row.members.include?(:major_minor)
  end
end
