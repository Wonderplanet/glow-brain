using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.AppAppliedBalanceDialog.Presentation
{
    /// <summary>
    /// 121_メニュー
    /// 　121-8_その他
    /// 　　121-8-8_アプリ専用通貨残高確認
    /// </summary>
    public class AppAppliedBalanceDialogView : UIView
    {
        [SerializeField] UIText freeDiamondText;
        [SerializeField] UIText paidDiamondText;
        [SerializeField] UIText allDiamondText;

        public void Initialize(AppAppliedBalanceDialogViewModel model)
        {
            freeDiamondText.SetText(model.FreeDiamond.ToStringSeparated());
            paidDiamondText.SetText(model.PaidDiamond.ToStringSeparated());
            allDiamondText.SetText(model.TotalDiamond.ToStringSeparated());
        }
    }
}
