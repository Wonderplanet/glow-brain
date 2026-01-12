require 'rails_helper'

RSpec.describe "OprGachaSaleTranslator" do
  subject { OprGachaSaleTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, opr_gacha_id: 1, start_at: 2, end_at: 3, primary_daily_free_play_count: 4, secondary_daily_free_play_count: 5)}

    it do
      view_model = subject
      expect(view_model.is_a?(OprGachaSaleViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.opr_gacha_id).to eq use_case_data.opr_gacha_id
      expect(view_model.start_at).to eq use_case_data.start_at
      expect(view_model.end_at).to eq use_case_data.end_at
      expect(view_model.primary_daily_free_play_count).to eq use_case_data.primary_daily_free_play_count
      expect(view_model.secondary_daily_free_play_count).to eq use_case_data.secondary_daily_free_play_count
    end
  end
end
