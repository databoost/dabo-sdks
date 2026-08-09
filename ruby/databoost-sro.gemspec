# frozen_string_literal: true

Gem::Specification.new do |spec|
  spec.name          = 'databoost-sro'
  spec.version       = '0.1.0'
  spec.authors       = ['DataBoost']
  spec.summary       = 'Thin Ruby client for the DataBoost SRO HTTP ranking API'
  spec.description   = 'Stateful Sticky Relative Order client — dense 1…n sequences only; no ranking logic in-process.'
  spec.license       = 'Proprietary'
  spec.homepage      = 'https://github.com/databoost/dabo-sdks'
  spec.metadata      = { 'source_code_uri' => 'https://github.com/databoost/dabo-sdks' }
  spec.files         = Dir['lib/**/*.rb', 'README.md']
  spec.require_paths = ['lib']
  spec.required_ruby_version = '>= 3.1.0'
end
