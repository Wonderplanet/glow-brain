require 'rails_helper'

RSpec.describe "GameUpdateTranslator" do
  subject { GameUpdateTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, login_bonuses: 0, guest_tp_summary: 1, updated_mission: 2, mini_stories: 3)}

    it do
      view_model = subject
      expect(view_model.is_a?(GameUpdateViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.login_bonuses).to eq use_case_data.login_bonuses
      expect(view_model.guest_tp_summary).to eq use_case_data.guest_tp_summary
      expect(view_model.updated_mission).to eq use_case_data.updated_mission
      expect(view_model.mini_stories).to eq use_case_data.mini_stories
    end
  end
end
