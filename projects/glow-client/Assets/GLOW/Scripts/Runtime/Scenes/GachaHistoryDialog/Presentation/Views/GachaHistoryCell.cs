using System;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.GachaHistoryDialog.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;
using AmountFormatter = GLOW.Core.Presentation.Modules.AmountFormatter;

namespace GLOW.Scenes.GachaHistoryDialog.Presentation.Views
{
    public class GachaHistoryCell : UIObject
    {
         [SerializeField] Button _button;
         [SerializeField] UIText _gachaDrawDateText; 
         [SerializeField] UIText _gachaTitleText;
         [SerializeField] UIImage _costItemIcon;
         [SerializeField] UIText _costAmountText;
         [SerializeField] GameObject _adObject;
         
        public void Setup(GachaHistoryCellViewModel viewModel, Action onTapped)
        {
            _gachaDrawDateText.SetText(DateTimeOffsetFormatter.FormatDateTime(viewModel.GachaDrawDate.ToJst()));
            _gachaTitleText.SetText(viewModel.GachaName.Value);
                
            _button.onClick.RemoveAllListeners();
            _button.onClick.AddListener(onTapped.Invoke);

            // 広告ガシャの場合、広告アイコンを表示してコスト表示を非表示にする
            _adObject.SetActive(viewModel.IsAdDraw);
            _costItemIcon.Hidden = viewModel.IsAdDraw;
            _costAmountText.SetText("×{0}", AmountFormatter.FormatAmount(viewModel.CostAmount.ToInt));
            
            if (!viewModel.IsAdDraw)
            {
                UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_costItemIcon.Image, viewModel.PlayerResourceIconAssetPath.Value);
            }
        }
    }
}