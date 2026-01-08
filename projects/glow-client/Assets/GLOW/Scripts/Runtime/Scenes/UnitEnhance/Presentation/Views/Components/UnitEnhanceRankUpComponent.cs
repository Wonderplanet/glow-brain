using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.UnitEnhance.Presentation.Views.Components
{
    public class UnitEnhanceRankUpComponent : UIObject
    {
        [SerializeField] List<UnitEnhanceRequireItemIconComponent> _rankUpCostIcons;

        public void Setup(
            UnitEnhanceRankUpViewModel viewModel,
            Action<ResourceType, MasterDataId, PlayerResourceAmount> onItemTapped)
        {
            for (int i = 0; i < viewModel.CostItems.Count && i < _rankUpCostIcons.Count; ++i)
            {
                _rankUpCostIcons[i].Hidden = false;
                _rankUpCostIcons[i].Setup(viewModel.CostItems[i], onItemTapped);
            }

            for(int i = viewModel.CostItems.Count; i < _rankUpCostIcons.Count; ++i)
            {
                _rankUpCostIcons[i].Hidden = true;
            }
        }
    }
}
