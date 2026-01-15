using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.BattleResult.Presentation.Components;
using GLOW.Scenes.PvpNewSeasonStart.Presentation.ViewModels;
using GLOW.Scenes.PvpTop.Domain.ValueObject;
using UIKit;
using UnityEngine;
using UnityEngine.Serialization;
using WonderPlanet.UniTaskSupporter;

namespace GLOW.Scenes.PvpNewSeasonStart.Presentation.Views
{
    public class PvpNewSeasonStartView : UIView
    {
        static readonly int TriggerIn = Animator.StringToHash("in");
        [SerializeField] Animator _animator;
        [SerializeField] RankingRankIcon _rankingRankIcon;
        [SerializeField] UIText _rankingRankText;
        [SerializeField] UIObject _closeButton;
        [SerializeField] VictoryResultTapLabelComponent _tapLabel;

        public void SetUp(PvpNewSeasonStartViewModel viewModel)
        {
            SetRankingRankIcon(viewModel.PvpRankClassType, viewModel.ScoreRankLevel);
            SetRankingRankText(viewModel.PvpRankClassType, viewModel.ScoreRankLevel);
            _closeButton.IsVisible = false;
            _tapLabel.IsVisible = false;

            DoAsync.Invoke(this.GetCancellationTokenOnDestroy(), async cancellationToken =>
            {
                await PlayStartAnimation(cancellationToken);
                _closeButton.IsVisible = true;
                _tapLabel.IsVisible = true;
                _tapLabel.Show("タップして閉じる");
            });
        }

        void SetRankingRankIcon(PvpRankClassType rank, ScoreRankLevel rankLevel)
        {
            _rankingRankIcon.SetupRankType(rank);
            _rankingRankIcon.PlayRankTierAnimation(rankLevel);
        }

        void SetRankingRankText(PvpRankClassType rankClass, ScoreRankLevel scoreRankLevel)
        {
            _rankingRankText.SetText("{0}からスタート!!", rankClass.ToDisplayStringWithRankLevel(scoreRankLevel));
        }


        async UniTask PlayStartAnimation(System.Threading.CancellationToken cancellationToken)
        {
            _animator.SetTrigger(TriggerIn);

            var stateInfo = _animator.GetCurrentAnimatorStateInfo(0);
            var waitTime = stateInfo.length > 0f ? stateInfo.length : 0.1f;
            await UniTask.Delay((int)(waitTime * 1000), cancellationToken: cancellationToken);
        }
    }
}
