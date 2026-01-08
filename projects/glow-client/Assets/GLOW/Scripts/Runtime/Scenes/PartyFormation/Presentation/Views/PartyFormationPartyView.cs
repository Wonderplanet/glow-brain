using System.Collections.Generic;
using System.Linq;
using System.Threading;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Presentation.Field;
using GLOW.Scenes.PartyFormation.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using WonderPlanet.UniTaskSupporter;

namespace GLOW.Scenes.PartyFormation.Presentation.Views
{
    public class PartyFormationPartyView : UIView
    {
        [SerializeField] PartyFormationPartyMemberComponent[] _partyMembers = new PartyFormationPartyMemberComponent[10];

        List<CancellationToken> _cancellationTokens = new ();

        public void RegisterLongPress(IPartyFormationUnitLongPressDelegate longPressDelegate)
        {
            foreach (var partyMember in _partyMembers)
            {
                partyMember.LongPressDelegate = longPressDelegate;
            }
        }

        public void Setup(PartyFormationPartyViewModel viewModel,
            IUnitImageLoader loader,
            IUnitImageContainer container)
        {
            CancelAllTokens();

            for (int i = 0; i < _partyMembers.Length; i++)
            {
                var partyMemberCell = _partyMembers[i];
                partyMemberCell.SetDefaultMode();
                partyMemberCell.SetLockSlot(viewModel.SlotCount <= i);
                partyMemberCell.SetIndex(i);
                var isAssignableSlot = viewModel.SpecialRulePartyUnitNum.IsInRange(i);
                if (i < viewModel.Members.Count && viewModel.Members[i].ImageAssetPath != UnitImageAssetPath.Empty)
                {
                    var memberViewModel = viewModel.Members[i];
                    SetupPartyView(partyMemberCell, memberViewModel, loader, container);
                    partyMemberCell.SetUpSpecialRuleBadge(!memberViewModel.IsAchievedSpecialRule || !isAssignableSlot);
                }
                else
                {
                    partyMemberCell.SetEmpty();
                    partyMemberCell.SetUpSpecialRuleBadge(!isAssignableSlot);
                }
            }
        }

        public void SetDefaultMode(PartyMemberIndex index)
        {
            if (index.IsEmpty()) return;
            _partyMembers[index.Value].SetDefaultMode();
        }

        public void SetDefaultMode(PartyMemberIndex min, PartyMemberIndex max)
        {
            if(min.IsEmpty() || _partyMembers.Length <= max.Value) return;
            for (int i = min.Value; i <= max.Value; ++i)
            {
                _partyMembers[i].SetDefaultMode();
            }
        }

        public void SetDefaultModeAll()
        {
            foreach (var partyMember in _partyMembers)
            {
                partyMember.SetDefaultMode();
            }
        }

        public void SetPreviewMode(PartyMemberIndex selectedUnitIndex, PartyMemberIndex currentSelectIndex)
        {
            var previewReferenceUnitIds = _partyMembers
                .Select(member => member.AssignedUserUnitId)
                .Where(id => !id.IsEmpty())
                .ToList();
            previewReferenceUnitIds.Remove(_partyMembers[selectedUnitIndex.Value].AssignedUserUnitId);
            if (currentSelectIndex.Value < previewReferenceUnitIds.Count)
            {
                previewReferenceUnitIds.Insert(currentSelectIndex.Value, UserDataId.Empty);
                SetPreviewModeForEmpty(currentSelectIndex);
            }
            else
            {
                previewReferenceUnitIds.Add(UserDataId.Empty);
                SetPreviewModeForEmpty(new PartyMemberIndex(previewReferenceUnitIds.Count-1));
            }

            for (int i = 0; i < previewReferenceUnitIds.Count; ++i)
            {
                if (previewReferenceUnitIds[i].IsEmpty()) continue;
                if (_partyMembers[i].AssignedUserUnitId == previewReferenceUnitIds[i]) continue;

                var referenceCell = _partyMembers.Find(member => member.AssignedUserUnitId == previewReferenceUnitIds[i]);
                if (null == referenceCell) continue;

                var prevCell = _partyMembers[i];
                prevCell.SetPreviewMode(referenceCell.ViewModel, referenceCell.SkeletonDataAsset, referenceCell.AvatarScale);
            }
        }

        public void SetPreviewModeForTargetFrameOut(PartyMemberIndex selectedUnitIndex)
        {
            SetDefaultMode(new PartyMemberIndex(0), selectedUnitIndex);

            for (int i = selectedUnitIndex.Value; i < _partyMembers.Length - 1 ; ++i)
            {
                var nextCell = _partyMembers[i + 1];
                if (nextCell.AssignedUserUnitId.IsEmpty())
                {
                    _partyMembers[i].SetPreviewModeForEmpty();
                    break;
                }
                _partyMembers[i].SetPreviewMode(nextCell.ViewModel, nextCell.SkeletonDataAsset, nextCell.AvatarScale);
            }

            // D&Dで枠外に持っていく場合編成から外すしょりになるので最後の枠は空になる
            _partyMembers.Last().SetPreviewModeForEmpty();

        }

        public void SetPreviewModeForEmpty(PartyMemberIndex index)
        {
            if (index.IsEmpty()) return;
            SetPreviewModeForEmpty(_partyMembers[index.Value].AssignedUserUnitId);
        }

        public void SetPreviewModeForEmpty(UserDataId userUnitId)
        {
            var partyMemberCell = _partyMembers.FirstOrDefault(unit => unit.AssignedUserUnitId == userUnitId);
            if (null == partyMemberCell) return;

            partyMemberCell.SetPreviewModeForEmpty();
        }

        void SetupPartyView(
            PartyFormationPartyMemberComponent partyMemberCell,
            PartyFormationPartyMemberViewModel memberViewModel,
            IUnitImageLoader loader,
            IUnitImageContainer container)
        {
            if (partyMemberCell.ImageAssetPath != memberViewModel.ImageAssetPath)
            {
                partyMemberCell.HiddenAvatar(true);
                DoAsync.Invoke(partyMemberCell, async cancellationToken =>
                {
                    _cancellationTokens.Add(cancellationToken);
                    await loader.Load(cancellationToken, memberViewModel.ImageAssetPath);
                    var prefab = container.Get(memberViewModel.ImageAssetPath);
                    var characterImage = prefab.GetComponent<UnitImage>();
                    var skeletonDataAsset = characterImage.SkeletonAnimation.skeletonDataAsset;
                    var avatarScale = characterImage.SkeletonScale;
                    partyMemberCell.SetupAvatar(skeletonDataAsset, avatarScale);
                    partyMemberCell.HiddenAvatar(false);
                    _cancellationTokens.Remove(cancellationToken);
                });
            }
            partyMemberCell.Setup(memberViewModel);
        }

        void CancelAllTokens()
        {
            foreach (var token in _cancellationTokens)
            {
                token.ThrowIfCancellationRequested();
            }
            _cancellationTokens.Clear();
        }

        public PartyMemberIndex GetMemberIndex(UserDataId userUnitId)
        {
            var index = _partyMembers.FindIndex(unit => unit.AssignedUserUnitId == userUnitId);
            return index == -1 ? PartyMemberIndex.Empty : new PartyMemberIndex(index);
        }

        public PartyMemberIndex GetCollisionIndex(Vector2 localPosition)
        {
            for(int i = 0 ; i < _partyMembers.Length ; ++i)
            {
                var rectTransform = (RectTransform)_partyMembers[i].transform;
                var rect = rectTransform.rect;
                rect.position = rectTransform.localPosition;
                rect.position -= rect.size/2;
                if (rect.Contains(localPosition))
                {
                    return new PartyMemberIndex(i);
                }
            }
            return PartyMemberIndex.Empty;
        }

        public void SetScrollMode(bool isScrollMode)
        {
            foreach (var partyMember in _partyMembers)
            {
                partyMember.SetStatusCanvasEnabled(!isScrollMode);
            }
        }
    }
}
