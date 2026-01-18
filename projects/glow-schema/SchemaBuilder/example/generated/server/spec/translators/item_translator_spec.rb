require 'rails_helper'

RSpec.describe "ItemTranslator" do
  subject { ItemTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, mst_item_id: 0, amount: 1)}

    it do
      view_model = subject
      expect(view_model.is_a?(ItemViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.mst_item_id).to eq use_case_data.mst_item_id
      expect(view_model.amount).to eq use_case_data.amount
    end
  end
end
