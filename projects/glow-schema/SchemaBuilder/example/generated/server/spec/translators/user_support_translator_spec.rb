require 'rails_helper'

RSpec.describe "UserSupportTranslator" do
  subject { UserSupportTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, my_id: 1, terminal_move_id: 2, terminal_move_password: 3, shop_birth_year: 4, shop_birth_month: 5, terms_of_service_version: 6, monthly_purchase_amount: 7)}

    it do
      view_model = subject
      expect(view_model.is_a?(UserSupportViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.my_id).to eq use_case_data.my_id
      expect(view_model.terminal_move_id).to eq use_case_data.terminal_move_id
      expect(view_model.terminal_move_password).to eq use_case_data.terminal_move_password
      expect(view_model.shop_birth_year).to eq use_case_data.shop_birth_year
      expect(view_model.shop_birth_month).to eq use_case_data.shop_birth_month
      expect(view_model.terms_of_service_version).to eq use_case_data.terms_of_service_version
      expect(view_model.monthly_purchase_amount).to eq use_case_data.monthly_purchase_amount
    end
  end
end
