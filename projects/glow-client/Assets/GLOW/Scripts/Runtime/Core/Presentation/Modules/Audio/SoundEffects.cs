using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

// ReSharper disable InconsistentNaming
namespace GLOW.Core.Presentation.Modules.Audio
{
    public static class SoundEffects
    {
        public static Dictionary<SoundEffectId, SoundEffect> Dictionary { get; } = new ()
        {
            {
                SoundEffectId.SSE_000_001,
                new SoundEffect(new SoundEffectAssetKey("SSE_000_001"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_000_002,
                new SoundEffect(new SoundEffectAssetKey("SSE_000_002"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_000_003,
                new SoundEffect(new SoundEffectAssetKey("SSE_000_003"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_000_004,
                new SoundEffect(new SoundEffectAssetKey("SSE_000_004"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_000_005,
                new SoundEffect(new SoundEffectAssetKey("SSE_000_005"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_000_007,
                new SoundEffect(new SoundEffectAssetKey("SSE_000_007"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_000_013,
                new SoundEffect(new SoundEffectAssetKey("SSE_000_013"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_000_015,
                new SoundEffect(new SoundEffectAssetKey("SSE_000_015"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_011_001,
                new SoundEffect(new SoundEffectAssetKey("SSE_011_001"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_012_001,
                new SoundEffect(new SoundEffectAssetKey("SSE_012_001"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_012_002,
                new SoundEffect(new SoundEffectAssetKey("SSE_012_002"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_012_003,
                new SoundEffect(new SoundEffectAssetKey("SSE_012_003"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_031_001,
                new SoundEffect(new SoundEffectAssetKey("SSE_031_001"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_031_002,
                new SoundEffect(new SoundEffectAssetKey("SSE_031_002"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_031_003,
                new SoundEffect(new SoundEffectAssetKey("SSE_031_003"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_031_004,
                new SoundEffect(new SoundEffectAssetKey("SSE_031_004"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_031_005,
                new SoundEffect(new SoundEffectAssetKey("SSE_031_005"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_031_006,
                new SoundEffect(new SoundEffectAssetKey("SSE_031_006"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_043_001,
                new SoundEffect(new SoundEffectAssetKey("SSE_043_001"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_043_002,
                new SoundEffect(new SoundEffectAssetKey("SSE_043_002"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_043_003,
                new SoundEffect(new SoundEffectAssetKey("SSE_043_003"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_043_004,
                new SoundEffect(new SoundEffectAssetKey("SSE_043_004"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_043_005,
                new SoundEffect(new SoundEffectAssetKey("SSE_043_005"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_043_006,
                new SoundEffect(new SoundEffectAssetKey("SSE_043_006"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_043_007,
                new SoundEffect(new SoundEffectAssetKey("SSE_043_007"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_043_008,
                new SoundEffect(new SoundEffectAssetKey("SSE_043_008"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_043_009,
                new SoundEffect(new SoundEffectAssetKey("SSE_043_009"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_043_010,
                new SoundEffect(new SoundEffectAssetKey("SSE_043_010"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_001,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_001"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_051_002,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_002"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_051_003,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_003"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_051_004,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_004"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_051_005,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_005"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_051_006,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_006"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_007,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_007"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_008,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_008"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_009,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_009"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_010,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_010"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_011,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_011"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_013,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_013"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_014,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_014"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_051_016,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_016"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_051_017,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_017"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_051_026,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_026"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_027,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_027"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_028,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_028"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_029,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_029"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_030,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_030"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_031,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_031"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_032,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_032"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_033,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_033"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_034,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_034"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_035,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_035"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_037,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_037"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_038,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_038"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_039,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_039"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_040,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_040"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_041,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_041"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_051_042,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_042"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_051_045,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_045"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_046,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_046"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_051_047,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_047"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_051_049,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_049"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_051_050,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_050"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_051_053,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_053"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_054,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_054"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_051_055,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_055"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_051_056,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_056"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_058,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_058"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_063,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_063"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_065,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_065"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_066,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_066"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_067,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_067"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_068,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_068"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_070,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_070"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_072,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_072"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_073,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_073"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_074,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_074"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_075,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_075"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_076,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_076"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_077,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_077"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_079,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_079"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_082,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_082"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_051_083,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_083"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_084,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_084"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_085,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_085"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_086,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_086"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_087,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_087"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_088,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_088"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_089,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_089"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_090,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_090"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_091,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_091"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_092,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_092"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_093,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_093"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_094,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_094"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_095,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_095"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_096,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_096"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_097,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_097"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_051_098,
                new SoundEffect(new SoundEffectAssetKey("SSE_051_098"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_053_001,
                new SoundEffect(new SoundEffectAssetKey("SSE_053_001"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_053_002,
                new SoundEffect(new SoundEffectAssetKey("SSE_053_002"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_053_004,
                new SoundEffect(new SoundEffectAssetKey("SSE_053_004"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_053_005,
                new SoundEffect(new SoundEffectAssetKey("SSE_053_005"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_053_008,
                new SoundEffect(new SoundEffectAssetKey("SSE_053_008"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_053_009,
                new SoundEffect(new SoundEffectAssetKey("SSE_053_009"), SoundEffectTag.InGame)
            },
            {
                SoundEffectId.SSE_053_010,
                new SoundEffect(new SoundEffectAssetKey("SSE_053_010"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_053_012,
                new SoundEffect(new SoundEffectAssetKey("SSE_053_012"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_053_015,
                new SoundEffect(new SoundEffectAssetKey("SSE_053_015"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_061_001,
                new SoundEffect(new SoundEffectAssetKey("SSE_061_001"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_061_002,
                new SoundEffect(new SoundEffectAssetKey("SSE_061_002"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_072_001,
                new SoundEffect(new SoundEffectAssetKey("SSE_072_001"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_072_002,
                new SoundEffect(new SoundEffectAssetKey("SSE_072_002"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_072_004,
                new SoundEffect(new SoundEffectAssetKey("SSE_072_004"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_072_005,
                new SoundEffect(new SoundEffectAssetKey("SSE_072_005"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_072_006,
                new SoundEffect(new SoundEffectAssetKey("SSE_072_006"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_072_007,
                new SoundEffect(new SoundEffectAssetKey("SSE_072_007"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_072_008,
                new SoundEffect(new SoundEffectAssetKey("SSE_072_008"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_072_010,
                new SoundEffect(new SoundEffectAssetKey("SSE_072_010"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_072_012,
                new SoundEffect(new SoundEffectAssetKey("SSE_072_012"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_072_014,
                new SoundEffect(new SoundEffectAssetKey("SSE_072_014"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_072_018,
                new SoundEffect(new SoundEffectAssetKey("SSE_072_018"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_072_019,
                new SoundEffect(new SoundEffectAssetKey("SSE_072_019"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_072_021,
                new SoundEffect(new SoundEffectAssetKey("SSE_072_021"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_072_022,
                new SoundEffect(new SoundEffectAssetKey("SSE_072_022"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_072_023,
                new SoundEffect(new SoundEffectAssetKey("SSE_072_023"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_072_025,
                new SoundEffect(new SoundEffectAssetKey("SSE_072_025"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_072_036,
                new SoundEffect(new SoundEffectAssetKey("SSE_072_036"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_072_038,
                new SoundEffect(new SoundEffectAssetKey("SSE_072_038"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_072_039,
                new SoundEffect(new SoundEffectAssetKey("SSE_072_039"), SoundEffectTag.Common)
            },
            {
                SoundEffectId.SSE_072_068,
                new SoundEffect(new SoundEffectAssetKey("SSE_072_068"), SoundEffectTag.Common)
            }
        };
    }
}
