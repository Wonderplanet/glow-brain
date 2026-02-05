using System;
using System.Globalization;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.StaminaRecover.Domain.ValueObject;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record MstConfigValue(ObscuredString Value)
    {
        public static MstConfigValue Empty = new MstConfigValue(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public int ToInt()
        {
            return int.Parse(Value, CultureInfo.InvariantCulture);
        }

        public decimal ToDecimal()
        {
            return decimal.Parse(Value, CultureInfo.InvariantCulture);
        }

        public long ToLong()
        {
            return long.Parse(Value, CultureInfo.InvariantCulture);
        }

        public MasterDataId ToMasterDataId()
        {
            return !IsEmpty() ? new MasterDataId(Value) : MasterDataId.Empty;
        }

        public RushCoefficient ToRushCoefficient()
        {
            return new RushCoefficient(ToDecimal());
        }

        public PercentageM ToPercentageM()
        {
            return new PercentageM(ToDecimal());
        }

        public UnitStatusExponent ToUnitStatusExponent()
        {
            return new UnitStatusExponent(ToDecimal());
        }

        public TickCount ToTickCount()
        {
            return new TickCount(ToLong());
        }

        public BuyStaminaAdCount ToBuyStaminaAdCount()
        {
            return new BuyStaminaAdCount(ToInt());
        }

        public AttackHitType ToAttackHitType()
        {
            return (AttackHitType)Enum.Parse(typeof(AttackHitType), Value, true);
        }

        public RecoveryStaminaMinutes ToRecoveryStaminaMinutes()
        {
            return new RecoveryStaminaMinutes(ToInt());
        }

        public StaminaRecoverPercentage ToStaminaRecoverPercentage()
        {
            return new StaminaRecoverPercentage(ToInt());
        }

        public UnitLevel ToUnitLevel()
        {
            return new UnitLevel(ToInt());
        }

        public ContinueCount ToContinueCount()
        {
            return new ContinueCount(ToInt());
        }

        public TotalDiamond ToTotalDiamond()
        {
            return new TotalDiamond(ToInt());
        }

        public BattlePoint ToBattlePoint()
        {
            return new BattlePoint(ToDecimal());
        }

        public KomaBackgroundAssetKey ToKomaBackgroundAssetKey()
        {
            return new KomaBackgroundAssetKey(Value);
        }

        public UnitAssetKey ToUnitAssetKey()
        {
            return new UnitAssetKey(Value);
        }
        
        public HookedPatternUrl ToHookedPatternUrl()
        {
            return new HookedPatternUrl(Value);
        }
    }
}
