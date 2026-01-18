require 'rails_helper'

RSpec.describe "UserProfileUpdateResultTranslator" do
  subject { UserProfileUpdateResultTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, updated_mission: 0)}

    it do
      view_model = subject
      expect(view_model.is_a?(UserProfileUpdateResultViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.updated_mission).to eq use_case_data.updated_mission
    end
  end
end
