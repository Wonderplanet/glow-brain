require 'rails_helper'

RSpec.describe "OprGachaTranslator" do
  subject { OprGachaTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, name: 1, asset_key: 2, start_at: 3, end_at: 4, sort_number: 5, banner_path: 6, special: 7, special_caption: 8, hide_if_shortage: 9, caution: 10, gacha_payment_type: 11, primary_payment_amount: 12, primary_draw_count: 13, primary_sr_bonus: 14, primary_ssr_bonus: 15, primary_caption: 16, primary_common_ticket_type: 17, secondary_payment_amount: 18, secondary_draw_count: 19, secondary_sr_bonus: 20, secondary_ssr_bonus: 21, secondary_caption: 22, secondary_common_ticket_type: 23, first_play_primary_gacha_free: 24, first_play_secondary_gacha_free: 25, primary_special_gacha_ticket_id: 26, secondary_special_gacha_ticket_id: 27, limit_play_count: 28, beginner_flg: 29, oha_payment_amount: 30, oha_caption: 31)}

    it do
      view_model = subject
      expect(view_model.is_a?(OprGachaViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.name).to eq use_case_data.name
      expect(view_model.asset_key).to eq use_case_data.asset_key
      expect(view_model.start_at).to eq use_case_data.start_at
      expect(view_model.end_at).to eq use_case_data.end_at
      expect(view_model.sort_number).to eq use_case_data.sort_number
      expect(view_model.banner_path).to eq use_case_data.banner_path
      expect(view_model.special).to eq use_case_data.special
      expect(view_model.special_caption).to eq use_case_data.special_caption
      expect(view_model.hide_if_shortage).to eq use_case_data.hide_if_shortage
      expect(view_model.caution).to eq use_case_data.caution
      expect(view_model.gacha_payment_type).to eq use_case_data.gacha_payment_type
      expect(view_model.primary_payment_amount).to eq use_case_data.primary_payment_amount
      expect(view_model.primary_draw_count).to eq use_case_data.primary_draw_count
      expect(view_model.primary_sr_bonus).to eq use_case_data.primary_sr_bonus
      expect(view_model.primary_ssr_bonus).to eq use_case_data.primary_ssr_bonus
      expect(view_model.primary_caption).to eq use_case_data.primary_caption
      expect(view_model.primary_common_ticket_type).to eq use_case_data.primary_common_ticket_type
      expect(view_model.secondary_payment_amount).to eq use_case_data.secondary_payment_amount
      expect(view_model.secondary_draw_count).to eq use_case_data.secondary_draw_count
      expect(view_model.secondary_sr_bonus).to eq use_case_data.secondary_sr_bonus
      expect(view_model.secondary_ssr_bonus).to eq use_case_data.secondary_ssr_bonus
      expect(view_model.secondary_caption).to eq use_case_data.secondary_caption
      expect(view_model.secondary_common_ticket_type).to eq use_case_data.secondary_common_ticket_type
      expect(view_model.first_play_primary_gacha_free).to eq use_case_data.first_play_primary_gacha_free
      expect(view_model.first_play_secondary_gacha_free).to eq use_case_data.first_play_secondary_gacha_free
      expect(view_model.primary_special_gacha_ticket_id).to eq use_case_data.primary_special_gacha_ticket_id
      expect(view_model.secondary_special_gacha_ticket_id).to eq use_case_data.secondary_special_gacha_ticket_id
      expect(view_model.limit_play_count).to eq use_case_data.limit_play_count
      expect(view_model.beginner_flg).to eq use_case_data.beginner_flg
      expect(view_model.oha_payment_amount).to eq use_case_data.oha_payment_amount
      expect(view_model.oha_caption).to eq use_case_data.oha_caption
    end
  end
end
