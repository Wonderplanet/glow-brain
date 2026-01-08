using System;
using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.PvpRewardList.Presentation.Component
{
    public class PvpRewardResourcesComponent : UIObject
    {
        [SerializeField] PlayerResourceIconButtonComponent[] _playerResourceIconComponents;

        public void SetUpRewards(
            IReadOnlyList<PlayerResourceIconViewModel> viewModels,
            Action<PlayerResourceIconViewModel> onTapped)
        {
            for (var i = 0; i < _playerResourceIconComponents.Length; i++)
            {
                if (i >= viewModels.Count)
                {
                    _playerResourceIconComponents[i].IsVisible = false;
                    continue;
                }

                _playerResourceIconComponents[i].IsVisible = true;
                var viewModel = viewModels[i];
                _playerResourceIconComponents[i].Setup(viewModel, () =>
                {
                    onTapped?.Invoke(viewModel);
                });
            }
        }
    }
}