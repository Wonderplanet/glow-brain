using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.AdventBattleRaidRankingResult.Presentation.Components;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.AdventBattleRaidRankingResult.Presentation.Views
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-4_降臨バトルランキング
    ///  　44-4-3_ランキング結果表示ダイアログ
    /// 　　　44-4-3-1_ランキング結果表示（協力バトル）ダイアログ
    /// </summary>
    public class AdventBattleRaidRankingResultView : UIView
    {
        [SerializeField] UIText _rankingTitleText;
        [SerializeField] UIText _scoreText;
        [SerializeField] PlayerResourceIconList _iconList;
        [SerializeField] AdventBattleRankingResultBossComponent _bossComponentPrefab;
        [SerializeField] RectTransform _bossComponentParent;

        [Header("Animation")]
        [SerializeField] Animator _resultPanelAnimator;

        AdventBattleRankingResultBossComponent _bossComponent;

        public void SetUpEnemyComponent(
            UnitImageAssetPath unitImageAssetPath,
            IUnitImageLoader loader,
            IUnitImageContainer container)
        {
            _bossComponent = Instantiate(_bossComponentPrefab, _bossComponentParent);
            if (unitImageAssetPath.IsEmpty())
            {
                _bossComponent.SetUpEmptyEnemyUnitImage();
            }
            else
            {
                _bossComponent.SetUpEnemyUnitImage(unitImageAssetPath, loader, container);
            }
        }

        public void SetUpTitle(AdventBattleName adventBattleName)
        {
            _rankingTitleText.SetText("{0}ランキング結果", adventBattleName.Value);
        }

        public void SetUpScoreText(AdventBattleScore score)
        {
            _scoreText.SetText(score.ToDisplayString());
        }

        public async UniTask PlaySlideInAnimation(CancellationToken cancellationToken)
        {
            _resultPanelAnimator.Play("SlideIn");
            await UniTask.WaitUntil(
                () => _resultPanelAnimator.GetCurrentAnimatorStateInfo(0).normalizedTime >= 1,
                cancellationToken:cancellationToken);
        }
        public void SkipSlideInAnimation()
        {
            _resultPanelAnimator.Play("SlideIn", 0, 1);
        }

        public async UniTask PlayEnemyIconAnimation(CancellationToken cancellationToken)
        {
            await _bossComponent.PlayEnemyIconAnimation(cancellationToken);
        }
        public void SkipEnemyIconAnimation()
        {
            _bossComponent.SkipEnemyIconAnimation();
        }

        public void PlayEnemyLoopAnimation()
        {
            _bossComponent.PlayEnemyLoopAnimation();
        }

        public async UniTask PlayRewardPanelAnimation(CancellationToken cancellationToken)
        {
            _resultPanelAnimator.Play("ExpandReward");
            await UniTask.WaitUntil(
                () => _resultPanelAnimator.GetCurrentAnimatorStateInfo(0).normalizedTime >= 1,
                cancellationToken:cancellationToken);
        }
        public void SkipRewardPanelAnimation()
        {
            _resultPanelAnimator.Play("ExpandReward", 0, 1);
        }

        public async UniTask PlayAcquiredItemsAnimation(
            IReadOnlyList<PlayerResourceIconViewModel> rewardViewModels,
            CancellationToken cancellationToken)
        {
            if (rewardViewModels.Count <= 0)
            {
                return;
            }

            var isCellAnimationCompleted = false;
            _iconList.SetupAndReload(rewardViewModels, true, 1, onComplete:() => isCellAnimationCompleted = true);

            await UniTask.WaitUntil(() => isCellAnimationCompleted, cancellationToken: cancellationToken);
        }
        public void SkipAcquiredItemsAnimation(IReadOnlyList<PlayerResourceIconViewModel> rewardViewModels)
        {
            _iconList.Hidden = false;
            _iconList.PlayerResourceIconAnimation?.SkipAnimation();

            // 0の時はonCompleteが呼ばれず待ちが終わらないため処理を飛ばす
            if (rewardViewModels.Count <= 0)
            {
                return;
            }
            
            _iconList.SetupAndReload(rewardViewModels, false);
        }
    }
}
