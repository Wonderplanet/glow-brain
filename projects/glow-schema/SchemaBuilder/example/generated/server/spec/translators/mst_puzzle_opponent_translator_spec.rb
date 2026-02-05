require 'rails_helper'

RSpec.describe "MstPuzzleOpponentTranslator" do
  subject { MstPuzzleOpponentTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, name: 1, emotion_element: 2, asset_key: 3, hp_coef: 4, attack_coef: 5, defence_coef: 6, boss_coef: 7, hp_split_count: 8, action_pattern_id: 9, action_count: 10)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstPuzzleOpponentViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.name).to eq use_case_data.name
      expect(view_model.emotion_element).to eq use_case_data.emotion_element
      expect(view_model.asset_key).to eq use_case_data.asset_key
      expect(view_model.hp_coef).to eq use_case_data.hp_coef
      expect(view_model.attack_coef).to eq use_case_data.attack_coef
      expect(view_model.defence_coef).to eq use_case_data.defence_coef
      expect(view_model.boss_coef).to eq use_case_data.boss_coef
      expect(view_model.hp_split_count).to eq use_case_data.hp_split_count
      expect(view_model.action_pattern_id).to eq use_case_data.action_pattern_id
      expect(view_model.action_count).to eq use_case_data.action_count
    end
  end
end
