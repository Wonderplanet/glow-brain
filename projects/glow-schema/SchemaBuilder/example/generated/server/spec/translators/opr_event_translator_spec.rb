require 'rails_helper'

RSpec.describe "OprEventTranslator" do
  subject { OprEventTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, asset_key: 1, mst_item_id: 2, start_at: 3, end_at: 4, display_at: 5, sort_number: 6)}

    it do
      view_model = subject
      expect(view_model.is_a?(OprEventViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.asset_key).to eq use_case_data.asset_key
      expect(view_model.mst_item_id).to eq use_case_data.mst_item_id
      expect(view_model.start_at).to eq use_case_data.start_at
      expect(view_model.end_at).to eq use_case_data.end_at
      expect(view_model.display_at).to eq use_case_data.display_at
      expect(view_model.sort_number).to eq use_case_data.sort_number
    end
  end
end
