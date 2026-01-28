require 'rails_helper'

RSpec.describe "CrystalTranslator" do
  subject { CrystalTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, free_amount: 0, paid_amount: 1)}

    it do
      view_model = subject
      expect(view_model.is_a?(CrystalViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.free_amount).to eq use_case_data.free_amount
      expect(view_model.paid_amount).to eq use_case_data.paid_amount
    end
  end
end
