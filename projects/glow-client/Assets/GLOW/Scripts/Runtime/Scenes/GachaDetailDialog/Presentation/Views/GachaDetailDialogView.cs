using GLOW.Core.Presentation.Components;
using GLOW.Scenes.GachaDetailDialog.Domain.Models;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.GachaDetailDialog.Presentation.Views
{
    /// <summary>
    /// 71-1_ガシャ
    /// 　71-1-8-2_ガシャ詳細ダイアログ
    /// </summary>
    public class GachaDetailDialogView : UIView
    {
        [SerializeField] UIToggleableComponentGroup _tabComponentGroup;
        [SerializeField] UIText _titleText;
        [SerializeField] RectTransform _contentTransform;

        public RectTransform ContentTransform => _contentTransform;

        public void HideTabComponentGroup()
        {
            _tabComponentGroup.Hidden = true;
        }

        public void SwitchTab(GachaDetailTabType gachaDetailTabType)
        {
            _tabComponentGroup.SetToggleOn(gachaDetailTabType.ToString());
        }
    }
}
