require 'rails_helper'

RSpec.describe "ReceivedPrizeHistoryTranslator" do
  subject { ReceivedPrizeHistoryTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, add_item_history: 0, add_crystal: 1, add_character_variant_history: 2)}

    it do
      view_model = subject
      expect(view_model.is_a?(ReceivedPrizeHistoryViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.add_item_history).to eq use_case_data.add_item_history
      expect(view_model.add_crystal).to eq use_case_data.add_crystal
      expect(view_model.add_character_variant_history).to eq use_case_data.add_character_variant_history
    end
  end
end
