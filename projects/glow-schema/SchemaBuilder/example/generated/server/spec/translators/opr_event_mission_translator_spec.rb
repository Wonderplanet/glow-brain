require 'rails_helper'

RSpec.describe "OprEventMissionTranslator" do
  subject { OprEventMissionTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, opr_event_id: 1, mission_number: 2, category: 3, description: 4, level: 5, goal_count: 6, opr_event_normal_quest_id: 7, prize: 8)}

    it do
      view_model = subject
      expect(view_model.is_a?(OprEventMissionViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.opr_event_id).to eq use_case_data.opr_event_id
      expect(view_model.mission_number).to eq use_case_data.mission_number
      expect(view_model.category).to eq use_case_data.category
      expect(view_model.description).to eq use_case_data.description
      expect(view_model.level).to eq use_case_data.level
      expect(view_model.goal_count).to eq use_case_data.goal_count
      expect(view_model.opr_event_normal_quest_id).to eq use_case_data.opr_event_normal_quest_id
      expect(view_model.prize).to eq use_case_data.prize
    end
  end
end
