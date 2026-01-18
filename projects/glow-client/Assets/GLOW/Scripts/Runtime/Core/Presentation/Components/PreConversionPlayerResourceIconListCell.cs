using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using WonderPlanet.UniTaskSupporter;

namespace GLOW.Core.Presentation.Components
{
    public class PreConversionPlayerResourceIconListCell :
        UICollectionViewCell,
        IPlayerResourceIconAnimationCell
    {
        [SerializeField] PreConversionPlayerResourceIconComponent _iconComponent;
        [SerializeField] Animator _animator;
        [SerializeField] string _appearanceAnimationName = "appear";

        public void Setup(PlayerResourceIconWithPreConversionViewModel viewModel)
        {
            _iconComponent.Setup(viewModel.PlayerResourceIcon);
            _iconComponent.SetConvertedPlayerResourceModel(viewModel.ConvertedPlayerResourceIcon);
        }

        public void PlayAppearanceAnimation(float normalizedTime = 0.0f)
        {
            _animator.Play(_appearanceAnimationName, 0, normalizedTime);
            SoundEffectPlayer.Play(SoundEffectId.SSE_000_005);

            DoAsync.Invoke(this.GetCancellationTokenOnDestroy(), async cancellationToken =>
            {
                await UniTask.Delay(
                    (int)(_animator.GetCurrentAnimatorStateInfo(0).length * 1000),
                    cancellationToken: cancellationToken);
                _iconComponent.PlayConvertAnimation();
            });
        }
    }
}
