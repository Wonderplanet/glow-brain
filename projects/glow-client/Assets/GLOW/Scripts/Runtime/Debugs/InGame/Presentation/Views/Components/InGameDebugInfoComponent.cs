using GLOW.Core.Presentation.Components;
using UnityEngine;
using UnityEngine.UI;
using WonderPlanet.PerformanceProfiler.Utils;

namespace GLOW.Debugs.InGame.Presentation.Views.Components
{
    public class InGameDebugInfoComponent : UIObject
    {
        [SerializeField] Text _fpsText;
        [SerializeField] Text _memoryUsageText;
        [SerializeField] Text _characterUnitCountText;

        public float FPS
        {
            set => _fpsText.text = value.ToString("N2");
        }

        public long MemoryUsage
        {
            set => _memoryUsageText.text = DataSizeConverter.ConvertToString((ulong)value, DataSizeUnits.Megabyte);
        }

        public int CharacterUnitCount
        {
            set => _characterUnitCountText.text = value.ToString();
        }
    }
}
