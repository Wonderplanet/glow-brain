using System;
using Cysharp.Text;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.BoxGacha.Presentation.ViewModel;
using UnityEngine;

namespace GLOW.Scenes.BoxGacha.Presentation.Component
{
    public class BoxGachaRewardIconComponent : UIObject
    {
        [SerializeField] PlayerResourceIconButtonComponent _resourceIconButton;
        [SerializeField] UIImage _pickupImage;
        [SerializeField] UIObject _allReceivedObject;
        [SerializeField] UIText _stockText;

        public void SetUpRewardIcon(
            BoxGachaPrizeCellViewModel viewModel,
            Action<PlayerResourceIconViewModel> onPrizeIconTapped)
        {
            _resourceIconButton.Setup(
                viewModel.PrizeResourceViewModel,
                () => onPrizeIconTapped?.Invoke(viewModel.PrizeResourceViewModel));
            _pickupImage.IsVisible = viewModel.IsPickUp;
            
            // 引いた数とストック数が同一なら全て獲得済み
            _allReceivedObject.IsVisible = viewModel.DrawCount == viewModel.Stock;
            _stockText.SetText(ZString.Format("{0}/{1}", viewModel.DrawCount.Value, viewModel.Stock.Value));
        }
    }
}