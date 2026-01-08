using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;
using AmountFormatter = GLOW.Core.Presentation.Modules.AmountFormatter;

namespace GLOW.Scenes.StaminaRecover.Presentation.StaminaDiamondRecoverConfirm
{
    public class StaminaDiamondRecoverConfirmView : UIView
    {
        [Header("説明文")]
        [SerializeField] UIText _headerText;
        [Header("不足テキスト")]
        [SerializeField] UIText _shortageText;
        [Header("無償石")]
        [SerializeField] UIText _beforeFreeDiamondText;
        [SerializeField] UIText _afterFreeDiamondText;
        [Header("有償石")]
        [SerializeField] UIText _beforePaidDiamondText;
        [SerializeField] UIText _afterPaidDiamondText;
        [Header("ボタン")]
        [SerializeField] UITextButton _confirmButton;
        [SerializeField] UITextButton _shopButton;

        public void SetViewModel(StaminaDiamondRecoverConfirmViewModel viewModel)
        {
            _headerText.SetText("プリズム{0}個使用して\nスタミナを{1}回復しますか？", viewModel.ConsumeDiamond.Value, viewModel.RecoverValue.Value);
            var attentionText = viewModel.IsShortage
                ? "※プリズムが不足しています"
                : "※無償プリズムから先に使用されます";

            _shortageText.SetText(attentionText);
            _shopButton.gameObject.SetActive(viewModel.IsShortage);
            _confirmButton.gameObject.SetActive(!viewModel.IsShortage);

            //桁の表示が必要
            _beforeFreeDiamondText.SetText(AmountFormatter.FormatAmount(viewModel.BeforeFreeDiamond.Value));
            _afterFreeDiamondText.SetText(AmountFormatter.FormatAmount(viewModel.AfterFreeDiamond.Value));

            _beforePaidDiamondText.SetText(AmountFormatter.FormatAmount(viewModel.BeforePaidDiamond.Value));
            _afterPaidDiamondText.SetText(AmountFormatter.FormatAmount(viewModel.AfterPaidDiamond.Value));

        }
    }
}
