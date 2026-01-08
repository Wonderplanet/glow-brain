using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;
using WonderPlanet.UniTaskSupporter;

namespace GLOW.Scenes.BattleResult.Presentation.Views
{
    public class VictoryRewardComponent : UIObject
    {
        [SerializeField] VictoryRewardCell _victoryRewardCell;
        [SerializeField] RectTransform _rewardRoot;

        CancellationTokenSource _cancellationTokenSource;
        List<VictoryRewardCell> _currentRewardCells = new List<VictoryRewardCell>();

        public Action<PlayerResourceIconViewModel> OnPlayerResourceIconTapped { get; set; }

        public void Setup(
            IReadOnlyList<PlayerResourceIconViewModel> acquiredPlayerResources,
            IReadOnlyList<IReadOnlyList<PlayerResourceIconViewModel>> acquiredPlayerResourcesGroupedByStaminaRap)
        {
            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
            _cancellationTokenSource = new CancellationTokenSource();

            // スタミナラップが1つ以下の場合は通常表示
            if (acquiredPlayerResourcesGroupedByStaminaRap.Count <= 1)
            {
                CreateRewardCell(acquiredPlayerResources, VictoryRewardCell.DisplayMode.None);
                return;
            }

            // スタミナラップごとに報酬を表示
            for (int i = 0; i < acquiredPlayerResourcesGroupedByStaminaRap.Count; i++)
            {
                CreateRewardCell(
                    acquiredPlayerResourcesGroupedByStaminaRap[i],
                    VictoryRewardCell.DisplayMode.Farming,
                    i + 1);
            }

            // 合計報酬を表示
            CreateRewardCell(acquiredPlayerResources, VictoryRewardCell.DisplayMode.Total);

            LayoutRebuilder.ForceRebuildLayoutImmediate(_rewardRoot);
        }

        public void PlayRewardCellAnimation()
        {
            foreach (var currentRewardCell in _currentRewardCells)
            {
                currentRewardCell.CellEnableFalse();
            }

            DoAsync.Invoke(_cancellationTokenSource.Token, async cancellationToken =>
            {
                await UniTask.Delay(TimeSpan.FromSeconds(0.2f), cancellationToken: cancellationToken);
                foreach (var currentRewardCell in _currentRewardCells)
                {
                    await currentRewardCell.PlayCellAnimation(cancellationToken);
                    await UniTask.Delay(TimeSpan.FromSeconds(0.1f), cancellationToken: cancellationToken);
                }
            });
        }

        public void SkipRewardCellAnimation()
        {
            foreach (var currentRewardCell in _currentRewardCells)
            {
                currentRewardCell.SkipCellAnimation(_cancellationTokenSource);
            }
        }

        void CreateRewardCell(
            IReadOnlyList<PlayerResourceIconViewModel> resources,
            VictoryRewardCell.DisplayMode displayMode,
            int farmingIndex = 0)
        {
            var cell = Instantiate(_victoryRewardCell, _rewardRoot);
            cell.Setup(resources, displayMode, farmingIndex);
            cell.CellEnableFalse();
            cell.OnPlayerResourceIconTapped = OnPlayerResourceIconTapped;
            _currentRewardCells.Add(cell);
        }

        protected override void OnDestroy()
        {
            base.OnDestroy();
            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
        }
    }
}
