#if GLOW_INGAME_DEBUG
using System;
using System.Collections.Generic;
using GLOW.Debugs.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models;
using UIKit;
using Zenject;

namespace GLOW.Debugs.InGame.Presentation.DebugIngameLogView
{
    public enum DebugUnitStatusType
    {
        PlayerField,
        EnemyField,
        PlayerDeck,
        EnemyDeck
    }

    public class DebugIngameLogViewerViewController : UIViewController<DebugIngameLogViewerView>
    {
        public class Argument
        {
            public Action OnClose { get; set; }
        }
        [Inject] IDebugIngameLogViewerViewDelegate ViewDelegate { get; }
        [Inject] Argument Args { get; }

        DebugUnitStatusType _currentDebugUnitStatusType;

        public DebugUnitStatusType CurrentDebugUnitStatusType => _currentDebugUnitStatusType;

        public override void ViewDidLoad()
        {
            _currentDebugUnitStatusType = DebugUnitStatusType.PlayerField;
            ViewDelegate.Init(PushDamageReport);
        }
        public override void ViewDidDisappear()
        {
            base.ViewDidDisappear();
            ViewDelegate.ViewDidDisappear();
        }

        public void UpdateTickCount(int tickCount)
        {
            ActualView.UpdateTickCount(tickCount);
        }
        public void UpdateUnitStatus(IReadOnlyList<CharacterUnitModel> players, IReadOnlyList<CharacterUnitModel> enemies)
        {
            if(_currentDebugUnitStatusType == DebugUnitStatusType.PlayerField)
            {
                ActualView.UpdateUnitStatus(players, _currentDebugUnitStatusType);
            }
            else if (_currentDebugUnitStatusType == DebugUnitStatusType.EnemyField)
            {
                ActualView.UpdateUnitStatus(enemies, _currentDebugUnitStatusType);
            }
        }

        public void UpdateDeckStatus(IReadOnlyList<DeckUnitModel> playerDecks, IReadOnlyList<DeckUnitModel> enemyDecks)
        {
            if (_currentDebugUnitStatusType == DebugUnitStatusType.PlayerDeck)
            {
                ActualView.UpdateDeckStatus(playerDecks, _currentDebugUnitStatusType);
            }
            else if (_currentDebugUnitStatusType == DebugUnitStatusType.EnemyDeck)
            {
                ActualView.UpdateDeckStatus(enemyDecks, _currentDebugUnitStatusType);
            }
        }

        public void PushDamageReport(DebugInGameLogDamageModel model)
        {
            ActualView.UpdateDamageLog(model);
        }

        [UIAction]
        void OnClose()
        {
            Args.OnClose?.Invoke();
            Dismiss(false);
        }

        [UIAction]
        void OnSwitchDamageReport()
        {
            ActualView.DamageReportArea.SetActive(!ActualView.DamageReportArea.activeSelf);
        }

        [UIAction]
        void OnSwitchDebugUnitStatus()
        {
            _currentDebugUnitStatusType = _currentDebugUnitStatusType switch
            {
                DebugUnitStatusType.PlayerField => DebugUnitStatusType.EnemyField,
                DebugUnitStatusType.EnemyField => DebugUnitStatusType.PlayerDeck,
                DebugUnitStatusType.PlayerDeck => DebugUnitStatusType.EnemyDeck,
                _ => DebugUnitStatusType.PlayerField
            };
        }

    }
}
#endif
