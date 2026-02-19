using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.BattleResult.Presentation.ViewModels;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.BattleResult.Presentation.Views.DefeatResult
{
    /// <summary>
    /// 53_バトルリザルト
    /// 　53-2_敗北リザルト
    /// 　　53-2-1_敗北画面
    /// </summary>
    public class DefeatResultView : UIView
    {
        [SerializeField] GameObject _defeatResultRoot;
        [SerializeField] TimelineAnimation _timelineAnimation;
        [SerializeField] UIText _resultTipsText;
        [SerializeField] GameObject _closeScreenButton;
        [SerializeField] UIObject _closeTextRoot;
        [SerializeField] UIText _bossCountText;
        [SerializeField] UIText _remainingTargetEnemyCountText;
        [SerializeField] UIObject _bossCountRoot;
        [SerializeField] UIObject _remainingTargetEnemyCountRoot;
        [SerializeField] UIObject _retryRoot;
        [SerializeField] Button _retryButton;

        protected override void Awake()
        {
            base.Awake();
            _defeatResultRoot.SetActive(false);
            _closeScreenButton.SetActive(false);
            
            // 誤操作防止のため初期状態でinteractableをfalseにしておく
            _retryButton.interactable = false;
        }

        public void Setup(DefeatResultViewModel viewModel)
        {
            _resultTipsText.SetText(viewModel.Tips.Value);
                        
            _remainingTargetEnemyCountRoot.IsVisible = !viewModel.RemainingTargetEnemyCount.IsEmpty();
            _remainingTargetEnemyCountText.SetText(viewModel.RemainingTargetEnemyCount.Value.ToString());

            _bossCountRoot.IsVisible = !_remainingTargetEnemyCountRoot.IsVisible && !viewModel.TotalBossCount.IsZero();
            _bossCountText.SetText(
                "{0}/{1}", 
                GetDefeatedBossCount(viewModel.DefeatedBossCount), 
                GetTotalBossCountText(viewModel.TotalBossCount));
            _retryRoot.IsVisible = viewModel.IsRetryAvailable;
        }

        string GetDefeatedBossCount(DefeatBossEnemyCount defeatedBossCount)
        {
            if (defeatedBossCount > 999) return "999";
            return defeatedBossCount.Value.ToString();
        }

        string GetTotalBossCountText(BossCount totalBossCount)
        {
            if (totalBossCount.IsInfinity()) return "???";
            if (totalBossCount > 999) return "999";
            return totalBossCount.Value.ToString();
        }

        public void ActiveCloseButton()
        {
            _closeScreenButton.SetActive(true);
        }

        public void ActiveCloseText()
        {
            _closeTextRoot.Hidden = false;
        }

        public void SetActiveRetryButton(RetryAvailableFlag isRetryAvailable)
        {
            // アニメーション再生時に表示設定し、最後にinteractableを設定する
            _retryButton.interactable = isRetryAvailable;
        }

        public void PlayDefeatResultAnimation()
        {
            _defeatResultRoot.SetActive(true);
            _timelineAnimation.Play();
        }
    }
}
