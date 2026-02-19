using GLOW.Core.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    public class PreConversionPlayerResourceIconComponent : UIObject
    {
        [SerializeField] PlayerResourceIconComponent _iconComponent;
        [SerializeField] Animator _convertAnimator;
        const string _convertAnimationName = "GashaResultIconChange";

        PlayerResourceIconViewModel _convertedPlayerResourceModel =
            PlayerResourceIconViewModel.Empty;

        public void Setup(PlayerResourceIconViewModel playerResourceIconViewModel)
        {
            _convertAnimator.enabled = false;
            _iconComponent.Setup(playerResourceIconViewModel);
        }

        public void SetConvertedPlayerResourceModel(PlayerResourceIconViewModel convertedPlayerResourceModel)
        {
            _convertedPlayerResourceModel = convertedPlayerResourceModel;
        }

        public void PlayConvertAnimation()
        {
            if (_convertedPlayerResourceModel.IsEmpty()) return;

            _convertAnimator.enabled = true;
            _convertAnimator.Play(_convertAnimationName, 0, 0);
        }

        public void StopConvertAnimation()
        {
            _convertAnimator.Play(_convertAnimationName, 0, 0);
            _convertAnimator.Update(0);
            _convertAnimator.enabled = false;
        }

        void OnConvertAnimationAction()
        {
            if (_convertedPlayerResourceModel.IsEmpty()) return;

            // 変換アニメーション中にアイテムアイコンを切り替える
            _iconComponent.Setup(_convertedPlayerResourceModel);
        }
    }
}
