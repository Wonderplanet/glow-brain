using System;
using GLOW.Core.Domain.ValueObjects;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Debugs.Command.Presentations.Views.DebugMstUnitStatusView
{
    public class DebugMstUnitLevelStatusCell : MonoBehaviour
    {
        [SerializeField] Image _bg;
        [SerializeField] Text _levelText;
        public Image Bg => _bg;
        public Text LevelText => _levelText;

        //Cell Filter向け
        public UnitGrade UnitGrade { get; set; }
        public UnitRank UnitRank { get; set; }
        public UnitLevel UnitLevel { get; set; }
        public Action OnApplyFilter { get; set; }
    }
}
