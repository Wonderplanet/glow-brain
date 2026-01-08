using System.Collections.Generic;
using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.Home.Presentation.Presenters;
using GLOW.Scenes.Home.Presentation.ViewModels;
using UIKit;
using Zenject;
using UnityEngine;

namespace GLOW.Scenes.Home.Presentation.Views.HomeStageLimitStatusView
{
    public class HomeStageLimitStatusViewController : UIViewController<HomeStageLimitStatusView>
    {
        public class Argument
        {
            public PartyName PartyName { get; set; }
            public List<StageLimitStatusViewModel> HomeStageLimitStatusViewModels { get; set; }
        }
        [Inject]Argument Args { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ActualView.SetupEmpty();
            ActualView.SetPartyName(Args.PartyName);
            foreach (var viewModel in Args.HomeStageLimitStatusViewModels)
            {
                switch (viewModel.Status)
                {
                    case StageLimitPartyStatus.PartyUnitNum:
                        ActualView.SetupUnitCount(viewModel.PartyUnitNum);
                        break;
                    case StageLimitPartyStatus.PartyRarity:
                        ActualView.SetupUnitRarity(viewModel.Rarities);
                        break;
                    case StageLimitPartyStatus.PartySeries:
                        ActualView.SetupSeriesLogos(viewModel.SeriesLogImageAssetPathList);
                        break;
                    case StageLimitPartyStatus.PartyAttackRangeType:
                        ActualView.SetupAttackRange(viewModel.CharacterAttackRangeTypes);
                        break;
                    case StageLimitPartyStatus.PartyRoleType:
                        ActualView.SetupUnitRoleType(viewModel.CharacterUnitRoleTypes);
                        break;
                }
            }
        }

        [UIAction]
        void OnClose()
        {
            Dismiss();
        }
    }
}
