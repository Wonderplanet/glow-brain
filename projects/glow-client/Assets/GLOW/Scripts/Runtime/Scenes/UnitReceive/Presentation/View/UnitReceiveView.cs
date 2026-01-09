using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using GLOW.Scenes.InGame.Presentation.Field;
using GLOW.Scenes.UnitReceive.Presentation.Component;
using GLOW.Scenes.UnitReceive.Presentation.ViewModel;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.UnitReceive.Presentation.View
{
    public class UnitReceiveView : UIView
    {
        [SerializeField] Animator _animator;
        [SerializeField] UnitReceiveComponent unitReceiveComponent;
        [SerializeField] CanvasGroup _canvasGroup;
        [SerializeField] UIObject _closeButtonObject;

        static readonly int In = Animator.StringToHash("in");
        static readonly int Out = Animator.StringToHash("out");
        
        public void SetUp(
            UnitReceiveViewModel viewModel, 
            UnitImage unitImage)
        {
            _canvasGroup.alpha = 0.0f;
            unitReceiveComponent.SetUpUnitImage(unitImage);
            unitReceiveComponent.SetRarity(viewModel.Rarity);
            unitReceiveComponent.SetUpLogoImage(viewModel.SeriesLogoImagePath);
            unitReceiveComponent.SetNameText(viewModel.CharacterName);
            unitReceiveComponent.SetUnitColor(viewModel.CharacterColor);
            unitReceiveComponent.SetUpUnitPictureImage(viewModel.UnitCutInKomaAssetPath);
            unitReceiveComponent.SetUnitSpeechBalloon(viewModel.SpeechBalloonText);
        }
        
        public async UniTask PlayOpenAnimation(CancellationToken cancellationToken)
        {
            _animator.SetBool(In, true);
            
            await UniTask.WaitUntil(
                () =>  _animator.GetCurrentAnimatorStateInfo(0).normalizedTime >= 1.0f, 
                cancellationToken: cancellationToken);
            
            _closeButtonObject.IsVisible = true;
        }

        public async UniTask PlayCloseAnimation(CancellationToken cancellationToken)
        {
            _canvasGroup.alpha = 1.0f;
            _animator.SetBool(Out, true);
            
            await UniTask.WaitUntil(
                () =>  _animator.GetCurrentAnimatorStateInfo(0).normalizedTime >= 1.0f, 
                cancellationToken: cancellationToken);
        }
    }
}