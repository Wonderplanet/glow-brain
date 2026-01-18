using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Domain.ValueObjects.Pvp;
using UnityEngine;
using WonderPlanet.UniTaskSupporter;

namespace GLOW.Core.Presentation.Components
{
    public class RankingRankIcon : UIObject
    {
        [SerializeField] GameObject[] _rankIcons;
        [SerializeField] Animator _rankEffectAnimator;
        [SerializeField] CanvasGroup _rankIconCanvasGroup;

        const string RankEffectAnimationNameFormat = "Ef-Icon-Star-{0}-In";
        const string RankDefAnimationNameFormat = "Ef-Icon-Star-{0}-Standby";
        const string EmptyAnimationName = "Empty";

        public void SetupRankType(RankType rankType)
        {
            SetUpRankIconByIndex((int)rankType);
        }
        
        public void SetupRankType(PvpRankClassType rankType)
        {
            SetUpRankIconByIndex((int)rankType);
        }

        public async UniTask PlayRankTypeAnimation(RankType rankType, CancellationToken cancellationToken)
        {
            await PlayRankTypeAnimationByIndex((int)rankType, cancellationToken);
        }
        
        public async UniTask PlayRankTypeAnimation(PvpRankClassType rankType, CancellationToken cancellationToken)
        {
            await PlayRankTypeAnimationByIndex((int)rankType, cancellationToken);
        }

        public void PlayRankTierAnimation(ScoreRankLevel rankTier)
        {
            // 0 ~ 4まで
            if (!rankTier.IsValid())
            {
                return;
            }

            PlayDefAnimation(rankTier.Value);
        }
        
        public void PlayRankTierAnimation(PvpRankLevel rankLevel)
        {
            // 0 ~ 4まで
            if (!rankLevel.IsValid())
            {
                return;
            }
            
            PlayDefAnimation(rankLevel.Value);
        }

        public async UniTask PlayRankTierUpAnimation(AdventBattleScoreRankLevel rankTier, CancellationToken cancellationToken)
        {
            // 1 ~ 4まで
            if (!rankTier.IsValid() || rankTier.IsZero())
            {
                return;
            }

            await PlayRankTierUpAnimation(rankTier.Value, cancellationToken);
        }
        
        public async UniTask PlayRankTierUpAnimation(PvpRankLevel rankLevel, CancellationToken cancellationToken)
        {
            // 1 ~ 4まで
            if (!rankLevel.IsValid() || rankLevel.IsZero())
            {
                return;
            }

            await PlayRankTierUpAnimation(rankLevel.Value, cancellationToken);
        }

        void SetUpRankIconByIndex(int index)
        {
            foreach (var rankIcon in _rankIcons)
            {
                rankIcon.gameObject.SetActive(false);
            }

            if (index < 0 || index >= _rankIcons.Length)
            {
                return;
            }

            _rankIcons[index].SetActive(true);
        }
        
        async UniTask PlayRankTypeAnimationByIndex(int index, CancellationToken cancellationToken)
        {
            await _rankIconCanvasGroup.DOFade(0, 0.1f)
                .SetEase(Ease.InExpo)
                .WithCancellation(cancellationToken);

            SetUpRankIconByIndex(index);

            await _rankIconCanvasGroup.DOFade(1, 0.1f)
                .SetEase(Ease.OutExpo)
                .WithCancellation(cancellationToken);
        }
        
        async UniTask PlayRankTierUpAnimation(int rankTier, CancellationToken cancellationToken)
        {
            var playAnimationName = ZString.Format(RankEffectAnimationNameFormat, rankTier);
            _rankEffectAnimator.Play(playAnimationName);

            await UniTask.WaitUntil(
                () => _rankEffectAnimator.GetCurrentAnimatorStateInfo(0).normalizedTime >= 0.1f,
                cancellationToken:cancellationToken);
        }

        void PlayDefAnimation(int level)
        {
            _rankEffectAnimator.Play(EmptyAnimationName);

            DoAsync.Invoke(this.destroyCancellationToken, async cancellationToken =>
            {
                await UniTask.NextFrame();
                var playAnimationName = ZString.Format(RankDefAnimationNameFormat, level);
                _rankEffectAnimator.Play(playAnimationName);
            });


        }
    }
}
