require 'rails_helper'

RSpec.describe "ReceivedPrizeTranslator" do
  subject { ReceivedPrizeTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, items: 0, crystal: 1, character_variants: 2, sent_present_box_flg: 3)}

    it do
      view_model = subject
      expect(view_model.is_a?(ReceivedPrizeViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.items).to eq use_case_data.items
      expect(view_model.crystal).to eq use_case_data.crystal
      expect(view_model.character_variants).to eq use_case_data.character_variants
      expect(view_model.sent_present_box_flg).to eq use_case_data.sent_present_box_flg
    end
  end
end
