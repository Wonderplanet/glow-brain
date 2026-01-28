require 'rails_helper'

RSpec.describe "DailyMissionTranslator" do
  subject { DailyMissionTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, mission_number: 0, counter: 1, playable_until: 2, reward_receivable_until: 3, reward_received: 4)}

    it do
      view_model = subject
      expect(view_model.is_a?(DailyMissionViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.mission_number).to eq use_case_data.mission_number
      expect(view_model.counter).to eq use_case_data.counter
      expect(view_model.playable_until).to eq use_case_data.playable_until
      expect(view_model.reward_receivable_until).to eq use_case_data.reward_receivable_until
      expect(view_model.reward_received).to eq use_case_data.reward_received
    end
  end
end
