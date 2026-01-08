using System;
using System.Collections.Generic;
using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.PassShop.Presentation.Component
{
    public class PassRewardsComponent : UIObject
    {
        [SerializeField] UIText _titleText;
        [SerializeField] PlayerResourceIconButtonComponent[] _rewardButtonComponents;

        public void SetupRewards(
            ShopPassRewardType rewardType,
            PassDurationDay durationDay,
            IReadOnlyList<PlayerResourceIconViewModel> viewModels,
            Action<PlayerResourceIconViewModel> iconTapAction)
        {
            for (var i = 0; i < _rewardButtonComponents.Length; i++)
            {
                if (i >= viewModels.Count)
                {
                    _rewardButtonComponents[i].Hidden = true;
                    continue;
                }
                
                _rewardButtonComponents[i].Hidden = false;

                var viewModel = viewModels[i];
                _rewardButtonComponents[i].Setup(viewModel, () =>
                {
                    iconTapAction(viewModel);
                });
            }
            
            _titleText.SetText(GetRewardTitle(rewardType, durationDay));
        }
        
        string GetRewardTitle(ShopPassRewardType rewardType, PassDurationDay durationDay)
        {
            switch (rewardType)
            {
                case ShopPassRewardType.Daily:
                    return ZString.Format("{0}日間毎日報酬", durationDay.Value);
                case ShopPassRewardType.Immediately:
                    return "購入報酬";
                default:
                    return "";
            }
        }
    }
}