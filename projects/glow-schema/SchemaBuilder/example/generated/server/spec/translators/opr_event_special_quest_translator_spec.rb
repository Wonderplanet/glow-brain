require 'rails_helper'

RSpec.describe "OprEventSpecialQuestTranslator" do
  subject { OprEventSpecialQuestTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, opr_event_id: 1, name: 2, stamina_consumption: 3, recommended_level: 4, opr_event_special_quest_puzzle_stage_id: 5, dependency_opr_event_special_quest_id: 6, prize: 7)}

    it do
      view_model = subject
      expect(view_model.is_a?(OprEventSpecialQuestViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.opr_event_id).to eq use_case_data.opr_event_id
      expect(view_model.name).to eq use_case_data.name
      expect(view_model.stamina_consumption).to eq use_case_data.stamina_consumption
      expect(view_model.recommended_level).to eq use_case_data.recommended_level
      expect(view_model.opr_event_special_quest_puzzle_stage_id).to eq use_case_data.opr_event_special_quest_puzzle_stage_id
      expect(view_model.dependency_opr_event_special_quest_id).to eq use_case_data.dependency_opr_event_special_quest_id
      expect(view_model.prize).to eq use_case_data.prize
    end
  end
end
