using System.Linq;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Presentation.Translators;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Modules.MessageView.Presentation.Constants;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.HomePartyFormation.Domain.UseCases;
using GLOW.Scenes.PartyFormation.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.Constants;
using GLOW.Scenes.PartyFormation.Domain.Models;
using GLOW.Scenes.PartyFormation.Domain.UseCases;
using GLOW.Scenes.PartyFormation.Presentation.ViewModels;
using GLOW.Scenes.PartyFormation.Presentation.Views;
using GLOW.Scenes.PartyNameEdit.Presentation.Views;
using GLOW.Scenes.UnitEnhance.Presentation.Views;
using GLOW.Scenes.UnitList.Domain.UseCases;
using GLOW.Scenes.UnitSortAndFilterDialog.Domain.UseCases;
using GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Translators;
using GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Views;
using GLOW.Scenes.UnitTab.Presentation.Interface;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.PartyFormation.Presentation.Presenters
{
    public class PartyFormationPresenter : IPartyFormationViewDelegate
    {
        [Inject] IPartyFormationViewController ViewController { get; }
        [Inject] GetPartyFormationUnitListUseCase GetUnitListUseCase { get; }
        [Inject] AssignPartyUnitUseCase AssignPartyUnitUseCase { get; }
        [Inject] UnassignedPartyUnitUseCase UnassignedPartyUnitUseCase { get; }
        [Inject] UpdateUnitListFilterUseCase UpdateUnitListFilterUseCase { get; }
        [Inject] InitializeTemporaryPartyUseCase InitializeTemporaryPartyUseCase { get; }
        [Inject] ApplyUpdatedPartyUseCase ApplyUpdatedPartyUseCase { get; }
        [Inject] UpdatePartyUnitListUseCase UpdatePartyUnitListUseCase { get; }
        [Inject] UpdateSelectPartyUseCase UpdateSelectPartyUseCase { get; }
        [Inject] GetUnitSortAndFilterUseCase GetUnitSortAndFilterUseCase { get; }
        [Inject] GetNextPartyMemberSlotConditionUseCase GetNextPartyMemberSlotConditionUseCase { get; }
        [Inject] SetupPartyFormationConditionalFilterUseCase SetupPartyFormationConditionalFilterUseCase { get; }
        [Inject] UnitSortAndFilterDialogViewModelTranslator UnitSortAndFilterDialogViewModelTranslator { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] IHomeFooterDelegate HomeFooterDelegate { get; }
        [InjectOptional] IUnitTabDelegate UnitTabDelegate { get; }
        [Inject] RecommendPartyFormationUseCase RecommendPartyFormationUseCase { get; }

        bool _isShowingUnitEnhance;
        bool _shouldRefreshPartyNo;
        PartySpecialUnitAssignLimit _specialUnitAssignLimit;
        UnitSortFilterCacheType _unitSortFilterCacheType;

        void IPartyFormationViewDelegate.OnViewDidLoad()
        {
            _unitSortFilterCacheType = SetupPartyFormationConditionalFilterUseCase.Setup(
                MasterDataId.Empty,
                InGameContentType.Stage);
            var model = InitializeTemporaryPartyUseCase.InitializeTemporaryParty(MasterDataId.Empty, InGameContentType.Stage);
            var viewModel = new PartyFormationInitializeViewModel(
                model.InitialPartyNo,
                model.ActivePartyMemberSlotCount,
                model.ActivePartyCount,
                model.ExistsSpecialRule,
                _unitSortFilterCacheType);
            _specialUnitAssignLimit = model.PartySpecialUnitAssignLimit;
            ViewController.InitializeView(viewModel);
        }

        void IPartyFormationViewDelegate.OnViewWillAppear(PartyNo partyNo)
        {
            // 一度非表示になった場合はホーム側PT編成で更新されている場合を考慮し、partyNoを再取得
            if (_shouldRefreshPartyNo)
            {
                var model = InitializeTemporaryPartyUseCase.InitializeTemporaryParty(
                    MasterDataId.Empty,
                    InGameContentType.Stage);
                partyNo = model.InitialPartyNo;
                ViewController.SetPartyNo(partyNo);
                _shouldRefreshPartyNo = false;
            }

            UpdatePartyView(partyNo);

            // グレードアップ、強化などして戻ったタイミング場合に情報を反映させるための対応
            UpdateUnitList(partyNo);
            ViewController.PlayUnitListCellAppearanceAnimation();
        }

        void IPartyFormationViewDelegate.OnViewWillDisappear()
        {
            ApplyUpdatedPartyUseCase.Apply();
            _shouldRefreshPartyNo = true;
        }

        void IPartyFormationViewDelegate.ShowUnitEnhanceView(UserDataId userUnitId, PartyNo currentPartyNo)
        {
            if (_isShowingUnitEnhance) return;

            _isShowingUnitEnhance = true;
            var unitListModel = GetUnitListUseCase.GetUnitListModel(
                currentPartyNo,
                _unitSortFilterCacheType,
                MasterDataId.Empty,
                InGameContentType.Stage);
            var userUnitIds = unitListModel.Units.Select(model => model.UserUnitId).ToList();
            var args = new UnitViewController.Argument(userUnitId, userUnitIds);
            var viewController = ViewFactory.Create<UnitViewController, UnitViewController.Argument>(args);
            HomeViewNavigation.TryPush(viewController, HomeContentDisplayType.BottomOverlap);

            DoAsync.Invoke(ViewController.GetCancellationTokenOnDestroy(), async cancellationToken =>
            {
                await UniTask.WaitUntil(viewController.View.IsDestroyed, cancellationToken: cancellationToken);
                HomeFooterDelegate.UpdateBadgeStatus();
                //TODO: 暫定対応...homeTOP > 編成ボタンを押すとZenjectExceptionになる対策
                UnitTabDelegate?.UpdateTabBadge();
                _isShowingUnitEnhance = false;
            });
        }

        void IPartyFormationViewDelegate.ShowPartyMemberSlotUnlockCondition(int index)
        {
            var model = GetNextPartyMemberSlotConditionUseCase.GetNextPartyMemberSlotCondition(new PartyMemberSlotCount(index+1));
            var text = TranslateReleaseRequiredText(model);
            CommonToastWireFrame.ShowScreenCenterToast(text);
        }

        void IPartyFormationViewDelegate.SelectAssignUnit(PartyNo partyNo, UserDataId userUnitId)
        {
            var checkValidAssignUnitType = AssignPartyUnitUseCase.CheckValidAssignUnit(partyNo, userUnitId);
            switch (checkValidAssignUnitType)
            {
                case PartyMemberAssignmentResultType.NotEmpty:
                    ShowNextPartyMemberSlotConditionToast();
                    return;
                case PartyMemberAssignmentResultType.SpecialUnitLimit:
                    ShowAssignLimitOverSpecialUnitToast();
                    return;
                case PartyMemberAssignmentResultType.Valid:
                default:
                    break;
            }

            AssignPartyUnitUseCase.AssignUnitToEmptyIndex(partyNo, userUnitId);
            UpdateUnitList(partyNo);
            UpdatePartyView(partyNo);
        }

        void IPartyFormationViewDelegate.SelectUnassignUnit(PartyNo partyNo, UserDataId userUnitId)
        {
            if (UnassignedPartyUnitUseCase.IsValidUnassignedUnit(partyNo, userUnitId))
            {
                UnassignedPartyUnitUseCase.UnassignedUnit(partyNo, userUnitId);
            }
            else
            {
                MessageViewUtil.ShowMessageWithClose(
                    "パーティ編成",
                    "キャラを１体以上\n編成してください");
            }
            UpdateUnitList(partyNo);
            UpdatePartyView(partyNo);
        }

        void IPartyFormationViewDelegate.DropPartyUnit(PartyNo partyNo, PartyMemberIndex index, UserDataId userUnitId)
        {
            AssignPartyUnitUseCase.InterruptAssignUnit(partyNo, index, userUnitId);
            UpdatePartyView(partyNo);
        }

        void IPartyFormationViewDelegate.PartyNameEdit(PartyNo partyNo)
        {
            var args = new PartyNameEditDialogViewController.Argument(partyNo);
            var viewController = ViewFactory.Create<
                PartyNameEditDialogViewController,
                PartyNameEditDialogViewController.Argument>(args);
            ViewController.PresentModally(viewController);
            DoAsync.Invoke(ViewController.GetCancellationTokenOnDestroy(), async cancellationToken =>
            {
                await UniTask.WaitUntil(viewController.View.IsDestroyed, cancellationToken: cancellationToken);
                UpdatePartyView(partyNo);
            });
        }

        void IPartyFormationViewDelegate.OnSortAscending(PartyNo partyNo)
        {
            UpdateUnitListFilterUseCase.UpdateSortOrder(UnitListSortOrder.Ascending, _unitSortFilterCacheType);
            SetUpPartyUnitList(partyNo);
        }

        void IPartyFormationViewDelegate.OnSortDescending(PartyNo partyNo)
        {
            UpdateUnitListFilterUseCase.UpdateSortOrder(UnitListSortOrder.Descending, _unitSortFilterCacheType);
            SetUpPartyUnitList(partyNo);
        }

        void IPartyFormationViewDelegate.OnSortAndFilter(PartyNo partyNo)
        {
            var useCaseModel = GetUnitSortAndFilterUseCase.GetUnitSortAndFilterDialogModel(_unitSortFilterCacheType);
            var viewModel = UnitSortAndFilterDialogViewModelTranslator.ToTranslate(
                useCaseModel,
                MasterDataId.Empty,
                InGameContentType.Stage);
            var argument = new UnitSortAndFilterDialogViewController.Argument(
                viewModel,
                null,
                () =>
                {
                    SetUpPartyUnitList(partyNo);
                    UpdateSortFilterButton();
                });
            var dialogViewController = ViewFactory.Create<
                UnitSortAndFilterDialogViewController,
                UnitSortAndFilterDialogViewController.Argument>(argument);
            ViewController.PresentModally(dialogViewController);
        }

        public void SetUpPartyUnitList(PartyNo currentParty)
        {
            UpdateSelectPartyUseCase.UpdateSelectParty(currentParty);
            UpdatePartyUnitListUseCase.UpdateUnitList(
                currentParty,
                MasterDataId.Empty,
                InGameContentType.Stage,
                _unitSortFilterCacheType);

            UpdateUnitList(currentParty);
            UpdatePartyView(currentParty);
        }

        void UpdatePartyView(PartyNo partyNo)
        {
            ViewController.UpdatePartyView(partyNo);
        }

        void UpdateUnitList(PartyNo currentParty)
        {
            var unitListModel = GetUnitListUseCase.GetUnitListModel(
                currentParty,
                _unitSortFilterCacheType,
                MasterDataId.Empty,
                InGameContentType.Stage);
            var viewModel = TranslateUnitListViewModel(unitListModel);

            ViewController.UpdateUnitList(viewModel);
        }

        void UpdateSortFilterButton()
        {
            var useCaseModel = GetUnitSortAndFilterUseCase.GetUnitSortAndFilterDialogModel(_unitSortFilterCacheType);
            ViewController.UpdateSortAndFilterButton(useCaseModel.CategoryModel.IsAnyFilter());
        }

        void ShowAssignLimitOverSpecialUnitToast()
        {
            var text = ZString.Format("スペシャルキャラは{0}体までしか編成できません", _specialUnitAssignLimit.ToInt());;
            CommonToastWireFrame.ShowScreenCenterToast(text);
        }

        void ShowNextPartyMemberSlotConditionToast()
        {
            var model = GetNextPartyMemberSlotConditionUseCase.GetNextPartyMemberSlotCondition();
            if (model.IsEmpty()) return;

            var text = TranslateReleaseRequiredText(model);
            CommonToastWireFrame.ShowScreenCenterToast(text);
        }

        string TranslateReleaseRequiredText(PartyFormationMemberNextUnlockSlotModel model)
        {
            return ZString.Format("{0}（{1}）\n{2}話をクリアでPT編成枠の{3}枠目が開放",
                model.QuestName.Value,
                DifficultyToStringConverter.DifficultyToString(model.Difficulty),
                model.StageNumber.Value,
                model.Count.Value);
        }

        PartyFormationUnitListViewModel TranslateUnitListViewModel(PartyFormationUnitListModel model)
        {
            var cellViewModels = model.Units
                .Select(TranslateUnitListCellViewModel)
                .ToList();

            return new PartyFormationUnitListViewModel(cellViewModels,
                model.CategoryModel);
        }

        PartyFormationUnitListCellViewModel TranslateUnitListCellViewModel(PartyFormationUnitListCellModel model)
        {
            return new PartyFormationUnitListCellViewModel(
                model.UserUnitId,
                CharacterIconViewModelTranslator.Translate(model.CharacterIcon),
                model.IsAssigned,
                model.IsSelectable,
                model.SortType,
                model.NotificationBadge,
                model.EventBonusPercentage,
                InGameSpecialRuleAchievedFlag.True,
                model.InGameSpecialRuleUnitStatusTargetFlag);
        }

        public void OnRecommendedFormation(PartyNo partyNo)
        {
            // 自動編成
            var isCompleteRecommendPartyFormation = RecommendPartyFormationUseCase.FormRecommendParty(
                partyNo,
                EventBonusGroupId.Empty,
                MasterDataId.Empty,
                InGameContentType.Stage,
                MasterDataId.Empty);
            if (!isCompleteRecommendPartyFormation)
            {
                MessageViewUtil.ShowMessageWithClose("確認", "編成条件を満たすキャラ\nを所持していません。");
            }
            else
            {
                UpdateUnitList(partyNo);
                UpdatePartyView(partyNo);
            }
        }
    }
}
