using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EncyclopediaUnitSpecialAttack.Presentation.AssetLoaders;
using GLOW.Scenes.EncyclopediaUnitSpecialAttack.Presentation.ViewModels;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using GLOW.Scenes.InGame.Presentation.Field;
using UIKit;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.EncyclopediaUnitSpecialAttack.Presentation.Views
{
    /// <summary>
    /// 91_図鑑
    /// 　91-3_作品別キャラ表示
    /// 　　91-3-1_ヒーローキャラ表示
    /// 　　　91-3-1-1_必殺ワザ再生
    /// </summary>
    public class UnitSpecialAttackPreviewViewController : UIViewController<UnitSpecialAttackPreviewView>, IEscapeResponder
    {
        public record Argument(MasterDataId MstUnitId);

        public Action OnClose;

        [Inject] IUnitSpecialAttackPreviewViewDelegate PreviewViewDelegate { get; }
        [Inject] IUnitImageLoader UnitImageLoader { get; }
        [Inject] IUnitImageContainer UnitImageContainer { get; }
        [Inject] IUnitAttackViewInfoSetLoader UnitAttackViewInfoSetLoader { get; }
        [Inject] IUnitAttackViewInfoSetContainer UnitAttackViewInfoSetContainer { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] IUnitSpecialAttackPreviewSoundEffectLoader UnitSpecialAttackPreviewSoundEffectLoader { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ActualView.OnTap = PreviewViewDelegate.OnEndAnimation;
            PreviewViewDelegate.OnViewDidLoad();

            EscapeResponderRegistry.Bind(this, ActualView);
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            OnClose?.Invoke();
        }

        public void Setup(UnitSpecialAttackPreviewViewModel previewViewModel)
        {
            PlaySpecialAttack(previewViewModel, View.GetCancellationTokenOnDestroy()).Forget();
        }

        async UniTask PlaySpecialAttack(
            UnitSpecialAttackPreviewViewModel previewViewModel,
            CancellationToken cancellationToken)
        {
            UnitAttackViewInfo attackViewInfo = null;

            try
            {
                if (!previewViewModel.UnitAssetKey.IsEmpty())
                {
                    await UnitAttackViewInfoSetLoader.Load(previewViewModel.UnitAssetKey, cancellationToken);
                    var unitAttackViewInfoSet = UnitAttackViewInfoSetContainer.GetUnitAttackViewInfo(previewViewModel.UnitAssetKey);

                    if (unitAttackViewInfoSet != null)
                    {
                        attackViewInfo = unitAttackViewInfoSet.SpecialAttackViewInfo;
                    }
                }

                await UnitSpecialAttackPreviewSoundEffectLoader.Load(attackViewInfo, cancellationToken);

                await UnitImageLoader.Load(cancellationToken, previewViewModel.UnitImageAssetPath);
                var prefab = UnitImageContainer.Get(UnitImageAssetPath.FromAssetKey(previewViewModel.UnitAssetKey));
                var unitImage = prefab.GetComponent<UnitImage>();

                ActualView.Setup(unitImage, previewViewModel.UnitColor, previewViewModel.IsRight, UnitImageContainer);

                await ActualView.Play(
                    previewViewModel.ChargeTime,
                    previewViewModel.ActionDuration,
                    previewViewModel.IsRight,
                    attackViewInfo,
                    previewViewModel.UnitColor,
                    previewViewModel.UnitAssetKey,
                    cancellationToken);
                await UniTask.Delay(TimeSpan.FromSeconds(0.8), cancellationToken: cancellationToken);

                PreviewViewDelegate.OnEndAnimation();
            }
            finally
            {
                UnitSpecialAttackPreviewSoundEffectLoader.Unload(attackViewInfo);
            }
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            PreviewViewDelegate.OnEndAnimation();
            return true;
        }
    }
}
