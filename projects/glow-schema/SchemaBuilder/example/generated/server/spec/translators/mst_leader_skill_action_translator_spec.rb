require 'rails_helper'

RSpec.describe "MstLeaderSkillActionTranslator" do
  subject { MstLeaderSkillActionTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, mst_leader_skill_id: 0, description_format: 1, skill_action_type: 2, bit_target: 3, character_target: 4, angry: 5, joy: 6, sad: 7, happy: 8, condition: 9, condition_value: 10, action_value: 11, debuff_probability: 12)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstLeaderSkillActionViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.mst_leader_skill_id).to eq use_case_data.mst_leader_skill_id
      expect(view_model.description_format).to eq use_case_data.description_format
      expect(view_model.skill_action_type).to eq use_case_data.skill_action_type
      expect(view_model.bit_target).to eq use_case_data.bit_target
      expect(view_model.character_target).to eq use_case_data.character_target
      expect(view_model.angry).to eq use_case_data.angry
      expect(view_model.joy).to eq use_case_data.joy
      expect(view_model.sad).to eq use_case_data.sad
      expect(view_model.happy).to eq use_case_data.happy
      expect(view_model.condition).to eq use_case_data.condition
      expect(view_model.condition_value).to eq use_case_data.condition_value
      expect(view_model.action_value).to eq use_case_data.action_value
      expect(view_model.debuff_probability).to eq use_case_data.debuff_probability
    end
  end
end
