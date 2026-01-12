require 'rails_helper'

RSpec.describe "SoloLiveUserTitleTranslator" do
  subject { SoloLiveUserTitleTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, opr_solo_live_id: 0, mst_character_variant_id: 1, rank: 2, score: 3)}

    it do
      view_model = subject
      expect(view_model.is_a?(SoloLiveUserTitleViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.opr_solo_live_id).to eq use_case_data.opr_solo_live_id
      expect(view_model.mst_character_variant_id).to eq use_case_data.mst_character_variant_id
      expect(view_model.rank).to eq use_case_data.rank
      expect(view_model.score).to eq use_case_data.score
    end
  end
end
