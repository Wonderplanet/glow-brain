using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using UnityEngine;
using WonderPlanet.UniTaskSupporter;

namespace GLOW.Scenes.BattleResult.Presentation.Views
{
    public class VictoryRewardCell : UIObject
    {
        [SerializeField] PlayerResourceIconListCell _playerResourceIconListCell;
        [SerializeField] RectTransform _iconRoot;
        [SerializeField] UIObject _farmingBand;
        [SerializeField] UIObject _totalBand;
        [SerializeField] UIText _farmingCountText;

        public enum DisplayMode
        {
            None,
            Farming,
            Total
        }

        const float CellInterval = 0.07f;

        List<PlayerResourceIconListCell> _cells = new List<PlayerResourceIconListCell>();

        public Action<PlayerResourceIconViewModel> OnPlayerResourceIconTapped { get; set; }

        public void Setup(
            IReadOnlyList<PlayerResourceIconViewModel> acquiredPlayerResources,
            DisplayMode displayMode,
            int farmingCount = 0)
        {
            foreach (var acquiredPlayerResource in acquiredPlayerResources)
            {
                var icon = Instantiate(_playerResourceIconListCell, _iconRoot);
                icon.Setup(acquiredPlayerResource);
                icon.SelectEvent.AddListener(() => OnPlayerResourceIconTapped(acquiredPlayerResource));
                _cells.Add(icon);
            }

            _farmingBand.IsVisible = displayMode == DisplayMode.Farming;
            _totalBand.IsVisible = displayMode == DisplayMode.Total;

            if (displayMode == DisplayMode.Farming)
            {
                _farmingCountText.SetText(ZString.Format("{0}周回目獲得報酬", farmingCount));
            }
        }

        public void CellEnableFalse()
        {
            foreach (var cell in _cells)
            {
                cell.SetEnable(false);
            }
        }

        public async UniTask PlayCellAnimation(CancellationToken cancellationToken)
        {
            foreach (var cell in _cells)
            {
                cell.PlayAppearanceAnimation();
                await UniTask.Delay(TimeSpan.FromSeconds(CellInterval), cancellationToken:cancellationToken);
            }

            var restRowCount = _cells.Count % 5;
            if (restRowCount > 0)
            {
                var delayTime = CellInterval * (5 - restRowCount);
                await UniTask.Delay(TimeSpan.FromSeconds(delayTime), cancellationToken: cancellationToken);
            }
        }

        public void SkipCellAnimation(CancellationTokenSource tokenSource)
        {
            foreach (var cell in _cells)
            {
                cell.PlayAppearanceAnimation(1.0f);
            }
            tokenSource?.Cancel();
        }
    }
}
