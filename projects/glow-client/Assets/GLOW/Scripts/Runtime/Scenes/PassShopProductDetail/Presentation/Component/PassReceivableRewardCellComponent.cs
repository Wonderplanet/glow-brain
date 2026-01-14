using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.PassShopProductDetail.Presentation.ViewModel;
using UnityEngine;

namespace GLOW.Scenes.PassShopProductDetail.Presentation.Component
{
    public class PassReceivableRewardCellComponent : UIObject
    {
        [SerializeField] PlayerResourceIconButtonComponent _playerResourceIconButtonComponent;
        [SerializeField] UIText _rewardNameText;
        [SerializeField] UIText _rewardAmountText;

        public void Setup(
            PassReceivableRewardViewModel rewardViewModel)
        {
            _playerResourceIconButtonComponent.Setup(rewardViewModel.PlayerResourceIconViewModel);
            _playerResourceIconButtonComponent.SetAmount(PlayerResourceAmount.Empty);

            _rewardNameText.SetText(rewardViewModel.ProductName.Value);
            _rewardAmountText.SetText(rewardViewModel.PlayerResourceIconViewModel.Amount.ToStringSeparated());
        }
    }
}
