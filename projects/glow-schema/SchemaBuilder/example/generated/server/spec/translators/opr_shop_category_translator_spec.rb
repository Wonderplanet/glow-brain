require 'rails_helper'

RSpec.describe "OprShopCategoryTranslator" do
  subject { OprShopCategoryTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, name: 1, asset_key: 2, button_size: 3, sort_number: 4)}

    it do
      view_model = subject
      expect(view_model.is_a?(OprShopCategoryViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.name).to eq use_case_data.name
      expect(view_model.asset_key).to eq use_case_data.asset_key
      expect(view_model.button_size).to eq use_case_data.button_size
      expect(view_model.sort_number).to eq use_case_data.sort_number
    end
  end
end
