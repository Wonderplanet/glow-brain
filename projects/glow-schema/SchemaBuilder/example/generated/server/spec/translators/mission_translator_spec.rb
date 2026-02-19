require 'rails_helper'

RSpec.describe "MissionTranslator" do
  subject { MissionTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, normal_missions: 0, daily_missions: 1, event_missions: 2, beginner_missions: 3)}

    it do
      view_model = subject
      expect(view_model.is_a?(MissionViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.normal_missions).to eq use_case_data.normal_missions
      expect(view_model.daily_missions).to eq use_case_data.daily_missions
      expect(view_model.event_missions).to eq use_case_data.event_missions
      expect(view_model.beginner_missions).to eq use_case_data.beginner_missions
    end
  end
end
