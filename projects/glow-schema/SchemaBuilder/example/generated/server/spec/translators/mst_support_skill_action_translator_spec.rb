require 'rails_helper'

RSpec.describe "MstSupportSkillActionTranslator" do
  subject { MstSupportSkillActionTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, mst_support_skill_id: 0, level: 1, description_format: 2, recast_turn: 3, skill_action_type: 4, bit_target: 5, character_target: 6, angry: 7, joy: 8, sad: 9, happy: 10, prob_angry: 11, prob_joy: 12, prob_sad: 13, prob_happy: 14, prob_heart: 15, action_value: 16, duration: 17)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstSupportSkillActionViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.mst_support_skill_id).to eq use_case_data.mst_support_skill_id
      expect(view_model.level).to eq use_case_data.level
      expect(view_model.description_format).to eq use_case_data.description_format
      expect(view_model.recast_turn).to eq use_case_data.recast_turn
      expect(view_model.skill_action_type).to eq use_case_data.skill_action_type
      expect(view_model.bit_target).to eq use_case_data.bit_target
      expect(view_model.character_target).to eq use_case_data.character_target
      expect(view_model.angry).to eq use_case_data.angry
      expect(view_model.joy).to eq use_case_data.joy
      expect(view_model.sad).to eq use_case_data.sad
      expect(view_model.happy).to eq use_case_data.happy
      expect(view_model.prob_angry).to eq use_case_data.prob_angry
      expect(view_model.prob_joy).to eq use_case_data.prob_joy
      expect(view_model.prob_sad).to eq use_case_data.prob_sad
      expect(view_model.prob_happy).to eq use_case_data.prob_happy
      expect(view_model.prob_heart).to eq use_case_data.prob_heart
      expect(view_model.action_value).to eq use_case_data.action_value
      expect(view_model.duration).to eq use_case_data.duration
    end
  end
end
