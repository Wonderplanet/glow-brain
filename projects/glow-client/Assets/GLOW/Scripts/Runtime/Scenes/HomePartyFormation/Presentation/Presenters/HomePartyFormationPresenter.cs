using System.Linq;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Presentation.Translators;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.HomePartyFormation.Domain.UseCases;
using GLOW.Scenes.HomePartyFormation.Presentation.Translators;
using GLOW.Scenes.HomePartyFormation.Presentation.Views;
using GLOW.Scenes.PartyFormation.Domain.Constants;
using GLOW.Scenes.PartyFormation.Domain.Models;
using GLOW.Scenes.PartyFormation.Domain.UseCases;
using GLOW.Scenes.PartyFormation.Presentation.ViewModels;
using GLOW.Scenes.PartyNameEdit.Presentation.Views;
using GLOW.Scenes.InGameSpecialRule.Presentation.ValueObjects;
using GLOW.Scenes.InGameSpecialRule.Presentation.Views;
using GLOW.Scenes.UnitEnhance.Presentation.Views;
using GLOW.Scenes.UnitList.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.UseCases;
using GLOW.Scenes.UnitSortAndFilterDialog.Domain.UseCases;
using GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Translators;
using GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Views;
using GLOW.Scenes.UnitTab.Presentation.Interface;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.HomePartyFormation.Presentation.Presenters
{
    public class HomePartyFormationPresenter : IHomePartyFormationVIewDelegate
    {
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] HomePartyFormationViewController.Argument Argument { get; }
        [Inject] HomePartyFormationUseCase HomePartyFormationUseCase { get; }
        [Inject] IHomePartyFormationViewController ViewController { get; }
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
        [Inject] IHomeFooterDelegate HomeFooterDelegate { get; }
        [InjectOptional] IUnitTabDelegate UnitTabDelegate { get; }
        [Inject] RecommendPartyFormationUseCase RecommendPartyFormationUseCase { get; }

        bool _isShowingUnitEnhance;
        PartySpecialUnitAssignLimit _specialUnitAssignLimit;

        UnitSortFilterCacheType _unitSortFilterCacheType;

        void IHomePartyFormationVIewDelegate.OnViewDidLoad()
        {
            _unitSortFilterCacheType = SetupPartyFormationConditionalFilterUseCase.Setup(
                Argument.SpecialRuleTargetMstStageId,
                Argument.SpecialRuleContentType);
            var model = InitializeTemporaryPartyUseCase.InitializeTemporaryParty(
                Argument.SpecialRuleTargetMstStageId,
                Argument.SpecialRuleContentType);
            var viewModel = new PartyFormationInitializeViewModel(
                model.InitialPartyNo,
                model.ActivePartyMemberSlotCount,
                model.ActivePartyCount,
                model.ExistsSpecialRule,
                _unitSortFilterCacheType);
            _specialUnitAssignLimit = model.PartySpecialUnitAssignLimit;

            ViewController.InitializeView(viewModel);
        }

        public void OnViewWillAppear(PartyNo partyNo)
        {
            // グレードアップ、強化などして戻ったタイミング場合に情報を反映させるための対応
            UpdateUnitList(partyNo);
            ViewController.PlayUnitListCellAppearanceAnimation();
        }

        void IHomePartyFormationVIewDelegate.OnViewDidUnload()
        {
            ApplyUpdatedPartyUseCase.Apply();
        }

        void IHomePartyFormationVIewDelegate.ShowUnitEnhanceView(UserDataId userUnitId, PartyNo currentPartyNo)
        {
            if (_isShowingUnitEnhance) return;

            _isShowingUnitEnhance = true;
            var unitListModel = GetUnitListUseCase.GetUnitListModel(
                currentPartyNo,
                _unitSortFilterCacheType,
                Argument.SpecialRuleTargetMstStageId,
                Argument.SpecialRuleContentType);
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

        void IHomePartyFormationVIewDelegate.ShowPartyMemberSlotUnlockCondition(int index)
        {
            var model = GetNextPartyMemberSlotConditionUseCase.GetNextPartyMemberSlotCondition(new PartyMemberSlotCount(index+1));
            var text = TranslateReleaseRequiredText(model);
            CommonToastWireFrame.ShowScreenCenterToast(text);
        }

        void IHomePartyFormationVIewDelegate.SelectAssignUnit(PartyNo partyNo, UserDataId userUnitId, bool isAchievedSpecialRule)
        {
            if (!isAchievedSpecialRule)
            {
                ShowInGameSpecialRule(InGameSpecialRuleFromUnitSelectFlag.True);
                return;
            }
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

        void IHomePartyFormationVIewDelegate.SelectUnassignUnit(PartyNo partyNo, UserDataId userUnitId)
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

        void IHomePartyFormationVIewDelegate.DropPartyUnit(PartyNo partyNo, PartyMemberIndex index, UserDataId userUnitId)
        {
            AssignPartyUnitUseCase.InterruptAssignUnit(partyNo, index, userUnitId);
            UpdateUnitList(partyNo);
            UpdatePartyView(partyNo);
        }

        void IHomePartyFormationVIewDelegate.PartyNameEdit(PartyNo partyNo)
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

        void IHomePartyFormationVIewDelegate.OnSortAscending(PartyNo partyNo)
        {
            UpdateUnitListFilterUseCase.UpdateSortOrder(UnitListSortOrder.Ascending, _unitSortFilterCacheType);
            SetUpPartyUnitList(partyNo);
        }

        void IHomePartyFormationVIewDelegate.OnSortDescending(PartyNo partyNo)
        {
            UpdateUnitListFilterUseCase.UpdateSortOrder(UnitListSortOrder.Descending, _unitSortFilterCacheType);
            SetUpPartyUnitList(partyNo);
        }

        void IHomePartyFormationVIewDelegate.OnSortAndFilter(PartyNo partyNo)
        {
            var useCaseModel = GetUnitSortAndFilterUseCase.GetUnitSortAndFilterDialogModel(_unitSortFilterCacheType);
            var viewModel = UnitSortAndFilterDialogViewModelTranslator.ToTranslate(
                useCaseModel,
                Argument.SpecialRuleTargetMstStageId,
                Argument.SpecialRuleContentType);
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
                Argument.SpecialRuleTargetMstStageId,
                Argument.SpecialRuleContentType,
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
                Argument.SpecialRuleTargetMstStageId,
                Argument.SpecialRuleContentType);
            var unitListViewModel = TranslateUnitListViewModel(unitListModel);

            var homePartyFormationModel = HomePartyFormationUseCase.CreateHomePartyFormationUseCaseModel(
                Argument.SpecialRuleTargetMstStageId,
                Argument.SpecialRuleContentType);
            var homePartyFormationViewModel =
                HomePartyFormationViewModelTranslator.TranslateHomePartyFormationViewModel(homePartyFormationModel);

            ViewController.UpdateUnitList(unitListViewModel, homePartyFormationViewModel);
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

        public void OnCloseButtonTapped()
        {
            HomeViewNavigation.TryPop();
        }

        void ShowInGameSpecialRule(InGameSpecialRuleFromUnitSelectFlag isFromUnitSelect)
        {
            var controller = ViewFactory.Create<InGameSpecialRuleViewController, InGameSpecialRuleViewController.Argument>(
                new InGameSpecialRuleViewController.Argument(
                    Argument.SpecialRuleTargetMstStageId,
                    Argument.SpecialRuleContentType,
                    isFromUnitSelect));
            ViewController.PresentModally(controller);
        }

        public void OnInGameSpecialRule()
        {
            var specialRuleTargetMstId = Argument.SpecialRuleTargetMstStageId;
            if (!specialRuleTargetMstId.IsEmpty())
            {
                ShowInGameSpecialRule(InGameSpecialRuleFromUnitSelectFlag.False);
            }
        }

        public void OnRecommendedFormation(PartyNo partyNo)
        {
            // 自動編成
            var isCompleteRecommendPartyFormation = RecommendPartyFormationUseCase.FormRecommendParty(
                partyNo,
                Argument.EventBonusGroupId,
                Argument.SpecialRuleTargetMstStageId,
                Argument.SpecialRuleContentType,
                Argument.EnhanceQuestId);
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
