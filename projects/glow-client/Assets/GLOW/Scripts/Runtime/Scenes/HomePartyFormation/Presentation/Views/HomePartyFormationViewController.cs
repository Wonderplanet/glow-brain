using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.HomePartyFormation.Presentation.Presenters;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Presentation.Field;
using GLOW.Scenes.PartyFormation.Presentation.ViewModels;
using GLOW.Scenes.PartyFormation.Presentation.Views;
using GLOW.Scenes.UnitTab.Presentation.Views.Components;
using UIKit;
using UnityEngine.EventSystems;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.HomePartyFormation.Presentation.Views
{
    /// <summary>
    /// PartyFormationのバリアント
    /// ホーム画面の編成ボタンから遷移した時に画面下部のタブがなく、フッターが戻るボタンになっている編成画面を表示する
    /// </summary>
    public class HomePartyFormationViewController : UIViewController<PartyFormationView>,
        IHomePartyFormationViewController,
        IPartyFormationUnitLongPressDelegate,
        IPartyFormationUnitSelectDelegate,
        IPartyFormationLongPressOverrideDelegate,
        IUnitListFilterAndSortDelegate
    {
        public record Argument(
            MasterDataId SpecialRuleTargetMstStageId,
            InGameContentType SpecialRuleContentType,
            EventBonusGroupId EventBonusGroupId,
            MasterDataId EnhanceQuestId);

        [Inject] IHomePartyFormationVIewDelegate ViewDelegate { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IUnitImageLoader UnitImageLoader { get; }
        [Inject] IUnitImageContainer UnitImageContainer { get; }
        [Inject] Argument Args { get; }

        UserDataId _selectedUserUnitId = UserDataId.Empty;
        PointerEventData _currentEventData;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);

            var partyNo = ActualView.PartyListComponent.GetCurrentPartyNo();
            ViewDelegate.OnViewWillAppear(partyNo);
        }

        public override void LoadView()
        {
            PrefabName = "HomePartyFormationView";
            base.LoadView();
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            ViewDelegate.OnViewDidUnload();
        }

        CancellationToken IHomePartyFormationViewController.GetCancellationTokenOnDestroy()
        {
            return View.GetCancellationTokenOnDestroy();
        }

        void IHomePartyFormationViewController.PresentModally(UIViewController controller)
        {
            base.PresentModally(controller);
        }

        void IHomePartyFormationViewController.InitializeView(PartyFormationInitializeViewModel viewModel)
        {
            ActualView.PartyListComponent.InitPageViews(ViewFactory,
                this,
                this,
                ViewDelegate.SetUpPartyUnitList,
                viewModel,
                Args.SpecialRuleTargetMstStageId,
                Args.EventBonusGroupId,
                Args.SpecialRuleContentType);

            ActualView.UnitListComponent.SelectDelegate = this;
            ActualView.PageView.LongPressOverrideDelegate = this;
            ActualView.ScrollRect.LongPressOverrideDelegate = this;
            ActualView.FilterAndSortComponent.Delegate = this;

            ActualView.SetInGameSpecialRuleButtonVisible(viewModel.ExistsSpecialRule);
        }

        void IHomePartyFormationViewController.UpdatePartyView(PartyNo partyNo)
        {
            ActualView.PartyListComponent.UpdatePartyView(partyNo);
        }

        void IHomePartyFormationViewController.UpdateUnitList(
            PartyFormationUnitListViewModel viewModel,
            HomePartyFormationViewModel homePartyFormationViewModel)
        {
            var viewModels = UpdateUnitViewModelsWithBadges(
                viewModel.Units,
                homePartyFormationViewModel.UnitList);
            ActualView.UnitListComponent.Setup(viewModels);
            ActualView.FilterAndSortComponent.Setup(
                viewModel.CategoryModel.SortOrder,
                viewModel.CategoryModel.IsAnyFilter());
        }

        void IHomePartyFormationViewController.PlayUnitListCellAppearanceAnimation()
        {
            ActualView.UnitListComponent.PlayCellAppearanceAnimation();
        }

        void IHomePartyFormationViewController.UpdateSortAndFilterButton(bool isAnyFilter)
        {
            ActualView.FilterAndSortComponent.UpdateSortAndFilterButton(isAnyFilter);
        }

        void IPartyFormationUnitLongPressDelegate.OnLongPress(
            PointerEventData eventData,
            UserDataId userUnitId,
            UnitImageAssetPath imageAssetPath)
        {
            if (IsLongPressMode()) return;

            EnableLongPressMode(userUnitId, eventData);

            DoAsync.Invoke(ActualView, async cancellationToken =>
            {
                ActualView.FloatingAvatarComponent.DisableAvatar();
                await UnitImageLoader.Load(cancellationToken, imageAssetPath);
                var prefab = UnitImageContainer.Get(imageAssetPath);
                var characterImage = prefab.GetComponent<UnitImage>();
                var skeletonDataAsset = characterImage.SkeletonAnimation.skeletonDataAsset;
                var avatarScale = characterImage.SkeletonScale;
                ActualView.FloatingAvatarComponent.SetAvatar(skeletonDataAsset, avatarScale);
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            });
        }

        bool IsLongPressMode()
        {
            return ActualView.ScrollRect.IsLongPressMode || ActualView.PageView.IsLongPressMode;
        }

        void EnableLongPressMode(UserDataId userUnitId, PointerEventData eventData)
        {
            _selectedUserUnitId = userUnitId;
            ActualView.ScrollRect.SetLongPressMode(true);
            ActualView.PageView.SetLongPressMode(true);
            ActualView.FloatingAvatarComponent.SetEnable(true);
            ActualView.FloatingAvatarComponent.SetAvatarPosition(eventData);
            ActualView.PartyListComponent.SetPreviewModeInCurrentParty(userUnitId, true);
            View.UserInteraction = false;
        }

        void DisableLongPressMode(UserDataId userUnitId)
        {
            ActualView.ScrollRect.SetLongPressMode(false);
            ActualView.PageView.SetLongPressMode(false);
            ActualView.FloatingAvatarComponent.SetEnable(false);
            ActualView.PartyListComponent.SetPreviewModeInCurrentParty(userUnitId, false);
            ActualView.UserInteraction = true;
        }

        bool IsDragging()
        {
            return ActualView.ScrollRect.IsDragging || ActualView.PageView.IsDragging;
        }

        void IPartyFormationUnitLongPressDelegate.OnLongPressUp(UserDataId userUnitId)
        {
            if (IsDragging()) return;

            if (!IsLongPressMode())
            {
                var partyNo = ActualView.PartyListComponent.GetCurrentPartyNo();
                ViewDelegate.SelectUnassignUnit(partyNo, userUnitId);
                SoundEffectPlayer.Play(SoundEffectId.SSE_031_006);
            }

            DisableLongPressMode(_selectedUserUnitId);
            _selectedUserUnitId = UserDataId.Empty;
        }

        void IPartyFormationUnitLongPressDelegate.OnPressLock(int index)
        {
            ViewDelegate.ShowPartyMemberSlotUnlockCondition(index);
        }

        void IPartyFormationUnitSelectDelegate.ShowUnitEnhanceView(UserDataId userUnitId)
        {
            OnForceEndDrag();
            var partyNo = ActualView.PartyListComponent.GetCurrentPartyNo();
            ViewDelegate.ShowUnitEnhanceView(userUnitId, partyNo);
            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
        }

        void IPartyFormationUnitSelectDelegate.SelectAssignUnit(UserDataId userUnitId, bool isAchievedSpecialRule)
        {
            OnForceEndDrag();
            var partyNo = ActualView.PartyListComponent.GetCurrentPartyNo();
            ViewDelegate.SelectAssignUnit(partyNo, userUnitId, isAchievedSpecialRule);
            SoundEffectPlayer.Play(SoundEffectId.SSE_000_001);
        }

        void IPartyFormationUnitSelectDelegate.SelectUnassignUnit(UserDataId userUnitId)
        {
            OnForceEndDrag();
            var partyNo = ActualView.PartyListComponent.GetCurrentPartyNo();
            ViewDelegate.SelectUnassignUnit(partyNo, userUnitId);
            SoundEffectPlayer.Play(SoundEffectId.SSE_000_003);
        }

        void IPartyFormationLongPressOverrideDelegate.OnBeginDrag(PointerEventData eventData)
        {
            _currentEventData = eventData;
            ActualView.FloatingAvatarComponent.OnBeginDragEvent(eventData);
        }

        void IPartyFormationLongPressOverrideDelegate.OnDrag(PointerEventData eventData)
        {
            // _currentEventData = eventData;
            ActualView.FloatingAvatarComponent.OnDragEvent(eventData);
            ActualView.PartyListComponent.OnDragEvent(_selectedUserUnitId, eventData);
        }

        void IPartyFormationLongPressOverrideDelegate.OnEndDrag(PointerEventData eventData)
        {
            _currentEventData = null;
            OnEndDrag(eventData);
        }

        void OnEndDrag(PointerEventData eventData)
        {
            ActualView.FloatingAvatarComponent.OnEndDragEvent(eventData);
            ActualView.PartyListComponent.OnDropAvatar(eventData, partyMemberIndex =>
            {
                var partyNo = ActualView.PartyListComponent.GetCurrentPartyNo();
                if (partyMemberIndex.IsEmpty())
                {
                    ViewDelegate.SelectUnassignUnit(partyNo, _selectedUserUnitId);
                }
                else
                {
                    ViewDelegate.DropPartyUnit(partyNo, partyMemberIndex, _selectedUserUnitId);
                }
            });

            DisableLongPressMode(_selectedUserUnitId);
            _selectedUserUnitId = UserDataId.Empty;
        }


        void OnForceEndDrag()
        {
            if(_currentEventData == null) return;
            OnEndDrag(_currentEventData);
        }

        List<PartyFormationUnitListCellViewModel> UpdateUnitViewModelsWithBadges(
            IReadOnlyList<PartyFormationUnitListCellViewModel> unitList,
            IReadOnlyDictionary<UserDataId, InGameSpecialRuleAchievedFlag> unitListBadges)
        {
            var result = new List<PartyFormationUnitListCellViewModel>();
            foreach (var viewModel in unitList)
            {
                var newViewModel = viewModel;
                if (unitListBadges.TryGetValue(viewModel.UserUnitId, out var badge))
                {
                    newViewModel = viewModel with { IsAchievedSpecialRule = badge };
                }
                result.Add(newViewModel);
            }
            return result;
        }

        void IUnitListFilterAndSortDelegate.OnSortAndFilter()
        {
            var partyNo = ActualView.PartyListComponent.GetCurrentPartyNo();
            ViewDelegate.OnSortAndFilter(partyNo);
        }
        void IUnitListFilterAndSortDelegate.OnSortAscending()
        {
            var partyNo = ActualView.PartyListComponent.GetCurrentPartyNo();
            ViewDelegate.OnSortAscending(partyNo);
        }
        void IUnitListFilterAndSortDelegate.OnSortDescending()
        {
            var partyNo = ActualView.PartyListComponent.GetCurrentPartyNo();
            ViewDelegate.OnSortDescending(partyNo);
        }

        [UIAction]
        protected void OnPartyNameEditButton()
        {
            var partyNo = ActualView.PartyListComponent.GetCurrentPartyNo();
            ViewDelegate.PartyNameEdit(partyNo);
        }

        [UIAction]
        protected void OnPartyScrollRightButton()
        {
            ActualView.PartyListComponent.NextPage();

        }

        [UIAction]
        protected void OnPartyScrollLeftButton()
        {
            ActualView.PartyListComponent.PrevPage();
        }

        [UIAction]
        void OnCloseButton()
        {
            ViewDelegate.OnCloseButtonTapped();
        }

        [UIAction]
        void OnInGameSpecialRule()
        {
            ViewDelegate.OnInGameSpecialRule();
        }

        [UIAction]
        void OnRecommendedFormationButton()
        {
            ViewDelegate.OnRecommendedFormation(ActualView.PartyListComponent.GetCurrentPartyNo());
        }
    }
}
