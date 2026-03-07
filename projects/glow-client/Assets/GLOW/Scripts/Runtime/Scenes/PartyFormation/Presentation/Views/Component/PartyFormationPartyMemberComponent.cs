using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.PartyFormation.Presentation.ViewModels;
using Spine.Unity;
using UnityEngine;
using WPFramework.Presentation.Components;

namespace GLOW.Scenes.PartyFormation.Presentation.Views
{
    public class PartyFormationPartyMemberComponent : MonoBehaviour
    {
        [SerializeField] LongPressRecognizer _longPressRecognizer;
        [SerializeField] UIObject _emptyIcon;
        [SerializeField] PartyFormationPartyMemberAvatarComponent _defaultAvatar;
        [SerializeField] PartyFormationPartyMemberAvatarComponent _previewAvatar;
        [SerializeField] UIObject _slotLockIcon;
        [SerializeField] UIObject _specialRuleBadge;

        PartyFormationPartyMemberViewModel _currentViewModel;

        public UnitImageAssetPath ImageAssetPath => _currentViewModel?.ImageAssetPath;
        public UserDataId AssignedUserUnitId => _currentViewModel?.UserUnitId ?? UserDataId.Empty;
        public PartyFormationPartyMemberViewModel ViewModel => _currentViewModel;
        public IPartyFormationUnitLongPressDelegate LongPressDelegate { get; set; }
        public SkeletonDataAsset SkeletonDataAsset { get; private set; }
        public Vector3 AvatarScale { get; private set; }

        int _index;

        void Awake()
        {
            SetEmpty();
        }

        public void SetEmpty()
        {
            _defaultAvatar.SetEmpty();
            _emptyIcon.IsVisible = true;
            _currentViewModel = null;
            SkeletonDataAsset = null;
            AvatarScale = Vector3.one;
            HiddenAvatar(true);
            _longPressRecognizer.PointerPress.RemoveAllListeners();
            _longPressRecognizer.PointerUp.RemoveAllListeners();
            _longPressRecognizer.PointerUp.AddListener(_ =>
            {
                if (_slotLockIcon.Hidden) return;
                LongPressDelegate?.OnPressLock(_index);
            });
        }

        public void SetDefaultMode()
        {
            _defaultAvatar.gameObject.SetActive(true);
            _previewAvatar.gameObject.SetActive(false);
        }

        public void SetPreviewMode(PartyFormationPartyMemberViewModel viewModel, SkeletonDataAsset skeleton, Vector3 avatarScale)
        {
            _defaultAvatar.gameObject.SetActive(false);
            _previewAvatar.gameObject.SetActive(true);
            _previewAvatar.Setup(viewModel);
            _previewAvatar.SetupAvatar(skeleton, avatarScale);
        }

        public void SetPreviewModeForEmpty()
        {
            _defaultAvatar.gameObject.SetActive(false);
            _previewAvatar.gameObject.SetActive(true);
            _previewAvatar.SetEmpty();
        }

        /// <summary>
        /// データはリセットせず、表示のみを非表示にする
        /// </summary>
        /// <param name="isHidden"></param>
        public void HiddenAvatar(bool isHidden)
        {
            _defaultAvatar.HiddenAvatar(isHidden);
        }

        public void SetLockSlot(bool isLocked)
        {
            _slotLockIcon.IsVisible = isLocked;
        }

        public void SetIndex(int index)
        {
            _index = index;
        }

        public void Setup(PartyFormationPartyMemberViewModel viewModel)
        {
            _currentViewModel = viewModel;

            _defaultAvatar.Setup(viewModel);
            _emptyIcon.IsVisible = false;
            RegisterLongPress();
        }

        public void SetUpSpecialRuleBadge(bool isVisible)
        {
            _specialRuleBadge.IsVisible = isVisible;
        }

        public void SetupAvatar(SkeletonDataAsset skeleton, Vector3 avatarScale)
        {
            SkeletonDataAsset = skeleton;
            AvatarScale = avatarScale;
            _defaultAvatar.SetupAvatar(skeleton, avatarScale);
            HiddenAvatar(false);
        }

        public void SetStatusCanvasEnabled(bool isEnabled)
        {
            _defaultAvatar.SetStatusCanvasEnabled(isEnabled);
        }

        void RegisterLongPress()
        {
            _longPressRecognizer.PointerPress.RemoveAllListeners();
            _longPressRecognizer.PointerUp.RemoveAllListeners();
            _longPressRecognizer.PointerPress.AddListener(eventData =>
            {
                LongPressDelegate?.OnLongPress(eventData, _currentViewModel.UserUnitId, _currentViewModel.ImageAssetPath);
            });
            _longPressRecognizer.PointerUp.AddListener(_ =>
            {
                LongPressDelegate?.OnLongPressUp(_currentViewModel.UserUnitId);
            });
        }
    }
}
