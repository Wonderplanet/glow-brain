using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.UnitEnhance.Presentation.Views.Components
{
    public class UnitEnhanceLevelUpComponent : UIObject
    {
        [SerializeField] UIText _levelUpCost;
        [SerializeField] UIText _levelUpCostSufficient;

        public void Setup(UnitEnhanceLevelUpViewModel tabViewModel)
        {
            _levelUpCost.SetText(tabViewModel.LevelUpCost.ToStringSeparated());
            _levelUpCostSufficient.SetText(tabViewModel.LevelUpCost.ToStringSeparated());

            _levelUpCost.Hidden = !tabViewModel.IsEnoughCost;
            _levelUpCostSufficient.Hidden = tabViewModel.IsEnoughCost;
        }
    }
}
