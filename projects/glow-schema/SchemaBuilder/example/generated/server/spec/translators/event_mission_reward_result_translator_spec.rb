require 'rails_helper'

RSpec.describe "EventMissionRewardResultTranslator" do
  subject { EventMissionRewardResultTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, received: 0, updated_mission: 1)}

    it do
      view_model = subject
      expect(view_model.is_a?(EventMissionRewardResultViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.received).to eq use_case_data.received
      expect(view_model.updated_mission).to eq use_case_data.updated_mission
    end
  end
end
