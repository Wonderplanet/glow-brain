require 'rails_helper'

RSpec.describe "OprSoloLiveTranslator" do
  subject { OprSoloLiveTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, mst_character_variant_id: 1, start_at: 2, end_at: 3, asset_key: 4, mst_live_house_id: 5, default_mst_music_id: 6, mst_drop_group_number: 7, rank_exp: 8, tp: 9, stamina_consumption: 10, gacha_setting: 11, opr_gacha_id: 12)}

    it do
      view_model = subject
      expect(view_model.is_a?(OprSoloLiveViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.mst_character_variant_id).to eq use_case_data.mst_character_variant_id
      expect(view_model.start_at).to eq use_case_data.start_at
      expect(view_model.end_at).to eq use_case_data.end_at
      expect(view_model.asset_key).to eq use_case_data.asset_key
      expect(view_model.mst_live_house_id).to eq use_case_data.mst_live_house_id
      expect(view_model.default_mst_music_id).to eq use_case_data.default_mst_music_id
      expect(view_model.mst_drop_group_number).to eq use_case_data.mst_drop_group_number
      expect(view_model.rank_exp).to eq use_case_data.rank_exp
      expect(view_model.tp).to eq use_case_data.tp
      expect(view_model.stamina_consumption).to eq use_case_data.stamina_consumption
      expect(view_model.gacha_setting).to eq use_case_data.gacha_setting
      expect(view_model.opr_gacha_id).to eq use_case_data.opr_gacha_id
    end
  end
end
