require 'rails_helper'

RSpec.describe "GachaOhaHistoryTranslator" do
  subject { GachaOhaHistoryTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, opr_gacha_id: 0, reset_at: 1)}

    it do
      view_model = subject
      expect(view_model.is_a?(GachaOhaHistoryViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.opr_gacha_id).to eq use_case_data.opr_gacha_id
      expect(view_model.reset_at).to eq use_case_data.reset_at
    end
  end
end
