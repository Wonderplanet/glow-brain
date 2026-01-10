using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Presentation.Field;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UIKit;
using WonderPlanet.UniTaskSupporter;
using Zenject;

namespace GLOW.Modules.UnitAvatarPageView.Presentation.Views
{
    public interface IUnitAvatarPageViewController
    {
        public record Argument(MasterDataId MstUnitId);
        void SetFlip(bool isFlip);
        void PlayLevelUpAnimation();
        void PlayWaitAnimation();
        void PlayMoveAnimation();
        void SetupAvatar(UnitImageAssetPath unitImageAssetPath, CharacterColor color, PhantomizedFlag isPhantomized);
    }

    public class UnitAvatarPageViewController : UIViewController<UnitAvatarPageView>, IUnitAvatarPageViewController
    {
        public record Argument(MasterDataId MstUnitId);

        [Inject] IUnitAvatarPageViewDelegate ViewDelegate { get; }
        [Inject] IUnitImageLoader UnitImageLoader { get; }
        [Inject] IUnitImageContainer UnitImageContainer { get; }

        // Viewが表示される前にアニメーションパラメータの変更が呼ばれたらキャッシュしておいて初期化後に適用する
        bool _isInitialized;
        CharacterUnitAnimation _initAnimation = CharacterUnitAnimation.Wait;
        bool _isInitFlip;

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            if (!_isInitialized)
            {
                ViewDelegate.OnViewWillAppear();
            }
            else
            {
                ActualView.PlayWaitAnimation();
            }
        }

        public void SetFlip(bool isFlip)
        {
            if (!_isInitialized)
            {
                _isInitFlip = isFlip;
                return;
            }
            ActualView.SetFlip(isFlip);
        }

        public void PlayLevelUpAnimation()
        {
            ActualView.PlayLevelUpAnimation();
        }

        public void PlayWaitAnimation()
        {
            if (!_isInitialized)
            {
                _initAnimation = CharacterUnitAnimation.Wait;
                return;
            }
            ActualView.PlayWaitAnimation();
        }

        public void PlayMoveAnimation()
        {
            if (!_isInitialized)
            {
                _initAnimation = CharacterUnitAnimation.Move;
                return;
            }
            ActualView.PlayMoveAnimation();
        }

        public void SetupAvatar(UnitImageAssetPath unitImageAssetPath, CharacterColor color, PhantomizedFlag isPhantomized)
        {
            ActualView.Avatar.Hidden = true;
            ActualView.FooterShadow.Setup(color);
            DoAsync.Invoke(ActualView, async cancellationToken =>
            {
                await UnitImageLoader.Load(cancellationToken, unitImageAssetPath);
                var prefab = UnitImageContainer.Get(unitImageAssetPath);
                var characterImage = prefab.GetComponent<UnitImage>();
                var skeletonDataAsset = characterImage.SkeletonAnimation.skeletonDataAsset;
                var avatarScale = characterImage.SkeletonScale;
                ActualView.Avatar.SetSkeleton(skeletonDataAsset);
                ActualView.Avatar.SetAvatarScale(avatarScale);
                ActualView.Avatar.SetPhantomized(isPhantomized);
                ActualView.Avatar.Hidden = false;

                _isInitialized = true;
                ActualView.SetFlip(_isInitFlip);
                if (_initAnimation == CharacterUnitAnimation.Move)
                {
                    ActualView.PlayMoveAnimation();
                }
                else
                {
                    ActualView.PlayWaitAnimation();
                }
            });
        }
    }
}
