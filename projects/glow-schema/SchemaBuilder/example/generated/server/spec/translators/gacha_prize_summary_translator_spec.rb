require 'rails_helper'

RSpec.describe "GachaPrizeSummaryTranslator" do
  subject { GachaPrizeSummaryTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, rarity_boxes: 0, ceiling_rarity_boxes: 1)}

    it do
      view_model = subject
      expect(view_model.is_a?(GachaPrizeSummaryViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.rarity_boxes).to eq use_case_data.rarity_boxes
      expect(view_model.ceiling_rarity_boxes).to eq use_case_data.ceiling_rarity_boxes
    end
  end
end
