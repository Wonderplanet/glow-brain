using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.UserEmblem.Domain.Models;
using GLOW.Scenes.UserEmblem.Domain.UseCases;
using GLOW.Scenes.UserEmblem.Presentation.ViewModels;
using GLOW.Scenes.UserEmblem.Presentation.Views;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Modules.Log;
using WPFramework.Presentation.InteractionControls;
using Zenject;

namespace GLOW.Scenes.UserEmblem.Presentation.Presenters
{
    public class UserEmblemPresenter : IUserEmblemViewDelegate
    {
        [Inject] UserEmblemViewController ViewController { get; }
        [Inject] GetUserEmblemModelUseCase GetUserEmblemModelUseCase { get; }
        [Inject] ApplyUserEmblemUseCase ApplyUserEmblemUseCase { get; }
        [Inject] GetUserEmblemBadgeUseCase GetUserEmblemBadgeUseCase { get; }
        [Inject] UpdateUserEmblemBadgeUseCase UpdateUserEmblemBadgeUseCase { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }

        EmblemType _currentTab;
        MasterDataId _selectedEmblemId;
        List<MasterDataId> _viewedEmblemIds;
        IReadOnlyList<HeaderUserEmblemCellViewModel> _seriesEmblemList;
        IReadOnlyList<HeaderUserEmblemCellViewModel> _eventEmblemList;

        void IUserEmblemViewDelegate.OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(UserEmblemPresenter), nameof(IUserEmblemViewDelegate.OnViewDidLoad));

            var model = GetUserEmblemModelUseCase.GetHeaderUserEmblemModel();
            var viewModel = ConvertToViewModel(model);
            _currentTab = EmblemType.Series;
            _viewedEmblemIds = GetUserEmblemBadgeUseCase.GetUserEmblemBadge();
            _selectedEmblemId = viewModel.CurrentEmblem.Id;
            _seriesEmblemList = viewModel.SeriesEmblemList;
            _eventEmblemList = viewModel.EventEmblemList;

            ViewController.SetCurrentEmblem(viewModel.CurrentEmblem);
            ViewController.SetTabButtonSelected(_currentTab, viewModel.IsSeriesTabBadge, viewModel.IsEventTabBadge);
            ViewController.Setup();
            ViewController.EmblemListReload(viewModel.SeriesEmblemList, _selectedEmblemId);
        }
        
        void IUserEmblemViewDelegate.OnViewDidAppear()
        {
            ApplicationLog.Log(nameof(UserEmblemPresenter), nameof(IUserEmblemViewDelegate.OnViewDidAppear));
            ViewController.PlayEmblemListCellAppearanceAnimation();
        }

        void IUserEmblemViewDelegate.OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(UserEmblemPresenter), nameof(IUserEmblemViewDelegate.OnViewDidUnload));
        }

        void IUserEmblemViewDelegate.OnCloseSelected()
        {
            DoAsync.Invoke(ViewController.View, ScreenInteractionControl, async cancellationToken =>
            {
                if (_currentTab == EmblemType.Series)
                {
                    UpdateBadges(_seriesEmblemList);
                }
                else
                {
                    UpdateBadges(_eventEmblemList);
                }
                UpdateUserEmblemBadgeUseCase.UpdateUserEmblemBadge(_viewedEmblemIds);
                await ApplyUserEmblemUseCase.ApplyUserEmblem(cancellationToken, _selectedEmblemId);
                HomeHeaderDelegate.UpdateStatus();
                HomeHeaderDelegate.UpdateBadgeStatus();
                ViewController.Dismiss();
            });
        }

        void IUserEmblemViewDelegate.OnSeriesTabSelected()
        {
            if (_currentTab == EmblemType.Series) return;

            UpdateBadges(_eventEmblemList);
            _currentTab = EmblemType.Series;
            UpdateUserEmblemBadgeUseCase.UpdateUserEmblemBadge(_viewedEmblemIds);
            var model = GetUserEmblemModelUseCase.GetHeaderUserEmblemModel();
            var viewModel = ConvertToViewModel(model);

            ViewController.SetTabButtonSelected(_currentTab, viewModel.IsSeriesTabBadge, viewModel.IsEventTabBadge);
            ViewController.EmblemListReload(viewModel.SeriesEmblemList, _selectedEmblemId);
            ViewController.PlayEmblemListCellAppearanceAnimation();
        }

        void IUserEmblemViewDelegate.OnEventTabSelected()
        {
            if (_currentTab == EmblemType.Event) return;

            UpdateBadges(_seriesEmblemList);
            _currentTab = EmblemType.Event;
            UpdateUserEmblemBadgeUseCase.UpdateUserEmblemBadge(_viewedEmblemIds);
            var model = GetUserEmblemModelUseCase.GetHeaderUserEmblemModel();
            var viewModel = ConvertToViewModel(model);

            ViewController.SetTabButtonSelected(_currentTab, viewModel.IsSeriesTabBadge, viewModel.IsEventTabBadge);
            ViewController.EmblemListReload(viewModel.EventEmblemList, _selectedEmblemId);
            ViewController.PlayEmblemListCellAppearanceAnimation();
        }

        void IUserEmblemViewDelegate.OnIconTapped(MasterDataId emblemId)
        {
            if (!_viewedEmblemIds.Contains(emblemId))
            {
                _viewedEmblemIds.Add(emblemId);
                UpdateUserEmblemBadgeUseCase.UpdateUserEmblemBadge(_viewedEmblemIds);
            }

            var model = GetUserEmblemModelUseCase.GetHeaderUserEmblemModel();
            var viewModel = ConvertToViewModel(model);

            HeaderUserEmblemCellViewModel currentEmblem = HeaderUserEmblemCellViewModel.Empty;
            if (emblemId != _selectedEmblemId)
            {
                currentEmblem = viewModel.SeriesEmblemList.FirstOrDefault(emblem => emblem.Id == emblemId) ??
                                viewModel.EventEmblemList.FirstOrDefault(emblem => emblem.Id == emblemId);
            }

            if (currentEmblem != null)
            {
                _selectedEmblemId = currentEmblem.Id;
                ViewController.SetCurrentEmblem(currentEmblem);
            }

            var emblemList = _currentTab == EmblemType.Series ? viewModel.SeriesEmblemList : viewModel.EventEmblemList;
            ViewController.EmblemListReload(emblemList, _selectedEmblemId);
        }

        HeaderUserEmblemViewModel ConvertToViewModel(HeaderUserEmblemModel model)
        {
            var seriesEmblemList = model.SeriesEmblemList.Select(emblem => new HeaderUserEmblemCellViewModel(
                emblem.Id,
                emblem.AssetPath,
                emblem.Description,
                emblem.Badge
            ))
                .ToList();

            var eventEmblemList = model.EventEmblemList.Select(emblem => new HeaderUserEmblemCellViewModel(
                emblem.Id,
                emblem.AssetPath,
                emblem.Description,
                emblem.Badge
            ))
                .ToList();

            var currentEmblem = HeaderUserEmblemCellViewModel.Empty;
            if (!model.CurrentEmblem.IsEmpty())
            {
                currentEmblem = new HeaderUserEmblemCellViewModel(
                    model.CurrentEmblem.Id,
                    model.CurrentEmblem.AssetPath,
                    model.CurrentEmblem.Description,
                    model.CurrentEmblem.Badge);
            }

            return new HeaderUserEmblemViewModel(currentEmblem, model.IsSeriesTabBadge, model.IsEventTabBadge, seriesEmblemList, eventEmblemList);
        }

        void UpdateBadges(IReadOnlyList<HeaderUserEmblemCellViewModel> viewModelList)
        {
            foreach (var eventEmblem in viewModelList)
            {
                if (!_viewedEmblemIds.Contains(eventEmblem.Id))
                {
                    _viewedEmblemIds.Add(eventEmblem.Id);
                }
            }
        }
    }
}
