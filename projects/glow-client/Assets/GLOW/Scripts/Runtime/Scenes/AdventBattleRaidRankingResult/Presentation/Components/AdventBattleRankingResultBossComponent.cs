using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Modules.Spine.Presentation;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Presentation.Field;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using Spine.Unity;
using UIKit;
using UnityEngine;
using WonderPlanet.UniTaskSupporter;

namespace GLOW.Scenes.AdventBattleRaidRankingResult.Presentation.Components
{
    public class AdventBattleRankingResultBossComponent : UIComponent
    {
        [SerializeField] UISpineWithOutlineAvatar _enemyUnitImage;
        [SerializeField] AvatarFooterShadowComponent _footerShadowComponent;

        [Header("Animation")]
        [SerializeField] Animator _bossClashAnimator;

        public void SetUpEmptyEnemyUnitImage()
        {
            HideUnitImage(true);
        }

        public void SetUpEnemyUnitImage(
            UnitImageAssetPath unitImageAssetPath,
            IUnitImageLoader loader,
            IUnitImageContainer container)
        {
            DoAsync.Invoke(this, async cancellationToken =>
            {
                HideUnitImage(true);
                await loader.Load(cancellationToken, unitImageAssetPath);
                var prefab = container.Get(unitImageAssetPath);
                var unitImage = prefab.GetComponent<UnitImage>();
                var skeletonDataAsset = unitImage.SkeletonAnimation.skeletonDataAsset;
                var unitImageScale = unitImage.SkeletonScale;
                SetUpUnitImage(skeletonDataAsset, unitImageScale);
            });
        }

        void HideUnitImage(bool isHidden)
        {
            _enemyUnitImage.Hidden = isHidden;
            _footerShadowComponent.Hidden = isHidden;
        }

        void SetUpUnitImage(SkeletonDataAsset skeleton, Vector3 unitImageScale)
        {
            HideUnitImage(false);
            _enemyUnitImage.SetAvatarScale(unitImageScale);

            _enemyUnitImage.SetSkeleton(skeleton);
            _enemyUnitImage.Animate(CharacterUnitAnimation.Wait.Name);
        }

        public async UniTask PlayEnemyIconAnimation(CancellationToken cancellationToken)
        {
            _bossClashAnimator.Play("BossClash");
            SoundEffectPlayer.Play(SoundEffectId.SSE_051_017);
            await UniTask.WaitUntil(
                () => _bossClashAnimator.GetCurrentAnimatorStateInfo(0).normalizedTime >= 1,
                cancellationToken:cancellationToken);

            _bossClashAnimator.Play("BossClashResult_in");
            await UniTask.WaitUntil(
                () => _bossClashAnimator.GetCurrentAnimatorStateInfo(0).normalizedTime >= 1,
                cancellationToken:cancellationToken);
        }
        public void SkipEnemyIconAnimation()
        {
            _bossClashAnimator.Play("BossClash", 0, 1);
            _bossClashAnimator.Play("BossClashResult_in", 0, 1);
        }

        public void PlayEnemyLoopAnimation()
        {
            _bossClashAnimator.Play("BossClashResult_loop");
        }
    }
}
