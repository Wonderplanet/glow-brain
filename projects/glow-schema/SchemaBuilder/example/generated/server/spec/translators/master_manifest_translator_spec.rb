require 'rails_helper'

RSpec.describe "MasterManifestTranslator" do
  subject { MasterManifestTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, hash: 0)}

    it do
      view_model = subject
      expect(view_model.is_a?(MasterManifestViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.hash).to eq use_case_data.hash
    end
  end
end
