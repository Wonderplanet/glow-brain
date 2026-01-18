require 'rails_helper'

RSpec.describe "ShopPurchaseResultTranslator" do
  subject { ShopPurchaseResultTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, received: 0, shop_limited_purchase_history: 1, updated_mission: 2, updated_solo_stories: 3)}

    it do
      view_model = subject
      expect(view_model.is_a?(ShopPurchaseResultViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.received).to eq use_case_data.received
      expect(view_model.shop_limited_purchase_history).to eq use_case_data.shop_limited_purchase_history
      expect(view_model.updated_mission).to eq use_case_data.updated_mission
      expect(view_model.updated_solo_stories).to eq use_case_data.updated_solo_stories
    end
  end
end
