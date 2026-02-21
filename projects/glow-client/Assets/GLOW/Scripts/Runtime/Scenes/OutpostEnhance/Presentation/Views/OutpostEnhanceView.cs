using System;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.OutpostEnhance.Presentation.ViewModels;
using GLOW.Scenes.OutpostEnhance.Presentation.Views.Component;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.OutpostEnhance.Presentation.Views
{
    public class OutpostEnhanceView : UIView
    {
        [SerializeField] OutpostEnhanceOutpostComponent _outpost;
        [SerializeField] OutpostEnhanceTypeButtonComponent _typeButton;
        [SerializeField] RectTransform _buttonRoot;
        [SerializeField] ChildScaler _enhancementButtonListChildScaler;
        [SerializeField] OutpostEnhanceWindowComponent _outpostEnhanceWindowComponent;
        [SerializeField] OutpostEnhanceAnimationComponent _outpostEnhanceAnimationComponent;
        [SerializeField] OutpostEnhanceArtworkListComponent _outpostEnhanceArtworkListComponent;
        [SerializeField] UIObject _touchGuard;
        [SerializeField] UIObject _grayOut;
        [SerializeField] UIText _outpostHp;
        [SerializeField] GameObject _newArtworkBadge;
        [SerializeField] Button _outpostEnhanceButton;
        [SerializeField] OutpostEnhanceArtworkChangeComponent _artworkChangeComponent;

        public bool IsHiddenArtworkList => _outpostEnhanceArtworkListComponent.Hidden;

        public void InstantiateOutpostEnhanceTypeButtonComponent(
            OutpostEnhanceTypeButtonViewModel viewModel,
            Action<OutpostEnhanceTypeButtonViewModel> windowEvent)
        {
            var button = Instantiate(_typeButton, _buttonRoot);
            button.Setup(viewModel, windowEvent);
        }

        public void SetupOutpostEnhanceTypeButtonComponentTexts(
            OutpostEnhanceViewModel viewModel,
            Action<OutpostEnhanceTypeButtonViewModel> windowEvent)
        {
            if (viewModel.Buttons.Count() != _buttonRoot.childCount) return;

            for (int i = 0; i < viewModel.Buttons.Count(); i++)
            {
                var button = _buttonRoot.GetChild(i).GetComponent<OutpostEnhanceTypeButtonComponent>();
                button.Setup(viewModel.Buttons.ElementAt(i), windowEvent);
            }
        }

        public void SetTouchGuard(bool isGuard)
        {
            _touchGuard.Hidden = !isGuard;
        }

        public void SetGrayOut(bool isGrayOut)
        {
            _grayOut.Hidden = !isGrayOut;
        }

        public void SetupEnhanceWindow(
            OutpostEnhanceTypeButtonViewModel model,
            Action<OutpostEnhanceTypeButtonViewModel> enhanceEvent)
        {
            _artworkChangeComponent.SetInteractableArtworkChangeButton(false);
            _outpostEnhanceWindowComponent.Hidden = false;
            _outpostEnhanceWindowComponent.Setup(model, enhanceEvent);
        }

        public void HideEnhanceWindow(bool isArtworkChangeButtonInteractable)
        {
            if (_outpostEnhanceWindowComponent.Hidden) return;

            _artworkChangeComponent.SetInteractableArtworkChangeButton(isArtworkChangeButtonInteractable);
            _outpostEnhanceWindowComponent.Hidden = true;
        }

        public void SetInteractableArtworkChangeButton(bool isInteractable)
        {
            _artworkChangeComponent.SetInteractableArtworkChangeButton(isInteractable);
        }

        public void SetSkipButtonAction(Action action)
        {
            _outpostEnhanceAnimationComponent.SetSkipButtonAction(action);
        }

        public async UniTask PlayEnhanceEffectAnimation(CancellationToken cancellationToken)
        {
            await _outpostEnhanceAnimationComponent.PlayEnhanceEffectAnimation(cancellationToken);
        }

        public async UniTask PlayEnhanceWindowAnimation(
            OutpostEnhanceResultViewModel model,
            CancellationToken cancellationToken)
        {
            await _outpostEnhanceAnimationComponent.PlayEnhanceWindowAnimation(model, cancellationToken);
        }

        public void SkipEnhanceEffectAnimation()
        {
            _outpostEnhanceAnimationComponent.SkipEnhanceEffectAnimation();
        }

        public void EndAnimation()
        {
            _outpostEnhanceAnimationComponent.EndAnimation();
        }

        public void PlayEnhanceButtonListCellAppearanceAnimation()
        {
            _enhancementButtonListChildScaler.Play();
        }

        public void ClearButtonChildren()
        {
            foreach (Transform child in _buttonRoot)
            {
                Destroy(child.gameObject);
            }
        }

        public void SetNewOutpostArtworkBadge(NotificationBadge badge)
        {
            _newArtworkBadge.SetActive(badge.Value);
        }

        public void SetOutpostArtwork(ArtworkAssetPath path)
        {
            _outpost.SetArtwork(path);
        }

        public void SetOutpostHp(HP hp)
        {
            _outpostHp.SetText(hp.ToString());
        }

        public void ShowArtworkList(
            IOutpostEnhanceArtworkListComponentDelegate artworkListDelegate,
            OutpostEnhanceArtworkListViewModel viewModel,
            bool playAnimation = true)
        {
            _outpostEnhanceArtworkListComponent.Delegate = artworkListDelegate;
            _outpostEnhanceArtworkListComponent.Hidden = false;

            _outpostEnhanceArtworkListComponent.Setup(viewModel);

            if (playAnimation)
            {
                _outpostEnhanceArtworkListComponent.PlayCellAppearanceAnimation();
            }

            _buttonRoot.gameObject.SetActive(false);
            _outpostEnhanceButton.gameObject.SetActive(true);
            _artworkChangeComponent.SetActiveArtworkChangeButton(false);
        }

        public void HideArtworkList()
        {
            _outpostEnhanceArtworkListComponent.Hidden = true;
            _buttonRoot.gameObject.SetActive(true);
            _outpostEnhanceButton.gameObject.SetActive(false);
            _artworkChangeComponent.SetActiveArtworkChangeButton(true);
        }
    }
}
