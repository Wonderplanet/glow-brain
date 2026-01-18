using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.IdleIncentiveTop.Presentation.ViewModels;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using GLOW.Scenes.InGame.Presentation.Field;
using UIKit;
using WonderPlanet.ResourceManagement;
using WonderPlanet.UniTaskSupporter;
using Zenject;

namespace GLOW.Scenes.IdleIncentiveTop.Presentation.Views
{
    public sealed class IdleIncentiveTopViewController :
        HomeBaseViewController<IdleIncentiveTopView>,
        IAsyncActivityControl
    {
        [Inject] IIdleIncentiveTopViewDelegate ViewDelegate { get; }
        [Inject] IUnitImageLoader UnitImageLoader { get; }
        [Inject] IUnitImageContainer UnitImageContainer { get; }
        [Inject] IUnitAttackViewInfoSetLoader UnitAttackViewInfoSetLoader { get; }
        [Inject] IUnitAttackViewInfoSetContainer UnitAttackViewInfoSetContainer { get; }
        [Inject] IAssetSource AssetSource { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewDidAppear()
        {
            base.ViewDidAppear();

            ViewDelegate.OnViewDidAppear();
        }

        public void Setup(IdleIncentiveTopViewModel topViewModel)
        {
            ActualView.Setup(topViewModel);
        }

        public void SetupBackground(KomaBackgroundAssetKey assetKey)
        {
            var assetPath = KomaBackgroundAssetPath.FromAssetKey(assetKey);
            ActualView.SetupBackground(assetPath);
        }

        public async UniTask SetAnimationCharacter(CancellationToken cancellationToken, IdleIncentiveTopCharacterViewModel characterViewModel)
        {
            UnitAttackViewInfo attackViewInfo = null;
            if (!characterViewModel.PlayerCharacterAssetKey.IsEmpty())
            {
                await UnitAttackViewInfoSetLoader.Load(characterViewModel.PlayerCharacterAssetKey, cancellationToken);
                var unitAttackViewInfoSet = UnitAttackViewInfoSetContainer.GetUnitAttackViewInfo(characterViewModel.PlayerCharacterAssetKey);

                if (unitAttackViewInfoSet != null)
                {
                    attackViewInfo = unitAttackViewInfoSet.NormalAttackViewInfo;
                }
            }
            // NOTE:ロード時に画面中央(グローバル0座標)にキャラが配置されてしまうので、キャラ素材は一番最後にロードする
            await UniTask.WhenAll(
                UnitImageLoader.Load(cancellationToken, characterViewModel.PlayerUnitImageAssetPath),
                UnitImageLoader.Load(cancellationToken, characterViewModel.EnemyUnitImageAssetPath),
                ActualView.InitializeBattleEffectManager(cancellationToken));

            var playerCharacterUnit = InstantiateCharacterImage(characterViewModel.PlayerUnitImageAssetPath);
            var enemyCharacterUnit = InstantiateCharacterImage(characterViewModel.EnemyUnitImageAssetPath);

            ActualView.SetupAnimation(playerCharacterUnit,
                enemyCharacterUnit,
                attackViewInfo,
                characterViewModel);
        }

        UnitImage InstantiateCharacterImage(UnitImageAssetPath imageAssetPath)
        {
            var go = UnitImageContainer.Get(imageAssetPath);
            if (go == null) return null;

            var characterImage = go.GetComponent<UnitImage>();
            characterImage.SortingOrder = 0;
            return characterImage;
        }

        public void UpdateElapsedTime(string elapsedTime, bool isMax)
        {
            ActualView.SetElapsedTime(elapsedTime, isMax);
        }

        public void UpdateReceiveInterval(string interval)
        {
            ActualView.UpdateReceiveInterval(interval);
        }

        public void UpdateRewardList(IdleIncentiveRewardListViewModel viewModel)
        {
            ActualView.UpdateRewardList(viewModel);
        }

        public void PlayRewardListCellAppearanceAnimation()
        {
            ActualView.PlayRewardListCellAppearanceAnimation();
        }

        void IAsyncActivityControl.ActivityBegin()
        {
            View.UserInteraction = false;
        }

        void IAsyncActivityControl.ActivityEnd()
        {
            View.UserInteraction = true;
        }

        [UIAction]
        void OnBackButtonTapped()
        {
            ViewDelegate.OnBackSelected();
        }

        [UIAction]
        public void OnReceiveRewardButtonTapped()
        {
            ViewDelegate.OnReceiveSelected();
        }

        [UIAction]
        public void OnQuickReceiveRewardButtontapped()
        {
            ViewDelegate.OnQuickReceiveSelected();
        }
    }
}
