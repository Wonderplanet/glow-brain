using System;
using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.AdventBattleRewardList.Presentation.Component
{
    public class AdventBattleRewardResourcesComponent : UIObject
    {
        [SerializeField] PlayerResourceIconButtonComponent[] _playerResourceIconComponents;

        public void SetupRewards(
            IReadOnlyList<PlayerResourceIconViewModel> viewModels,
            Action<PlayerResourceIconViewModel> onTapped)
        {
            for (var i = 0; i < _playerResourceIconComponents.Length; i++)
            {
                if (i >= viewModels.Count)
                {
                    _playerResourceIconComponents[i].Hidden = true;
                    continue;
                }

                _playerResourceIconComponents[i].Hidden = false;
                var viewModel = viewModels[i];
                _playerResourceIconComponents[i].Setup(viewModel, () =>
                {
                    onTapped?.Invoke(viewModel);
                });
            }
        }
    }
}