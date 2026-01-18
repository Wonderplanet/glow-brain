using GLOW.Core.Domain.Constants;
using UnityHTTPLibrary;
using GLOW.Core.Exceptions;
using Wonderplanet.IAP.Exception;
using WPFramework.Exceptions.Mappers;

namespace GLOW.Core.Domain.Mappers.Exception
{
    public sealed class ServerErrorExceptionMapper : IServerErrorExceptionMapper
    {
        public System.Exception Map(ServerErrorException exception)
        {
            if (exception.StatusCode != HTTPStatusCodes.ApplicationError)
            {
                return exception;
            }

            return exception.ServerErrorCode switch
            {
                (int)ServerErrorCode.ValidationError => new ValidationErrorException(exception),
                (int)ServerErrorCode.InvalidAccessToken => new InvalidAccessTokenException(exception),
                (int)ServerErrorCode.InvalidIdToken => new InvalidIdTokenException(exception),
                (int)ServerErrorCode.DeviceLinkAuthFailed => new DeviceLinkAuthFailedException(exception),
                (int)ServerErrorCode.UserCreateFailed => new UserCreateFailedException(exception),
                (int)ServerErrorCode.UserNotFound => new UserNotFoundException(exception),
                (int)ServerErrorCode.UnitNotFound => new UnitNotFoundException(exception),
                (int)ServerErrorCode.LackOfResources => new LackOfResourcesException(exception),
                (int)ServerErrorCode.MstNotFound => new MstNotFoundException(exception),
                (int)ServerErrorCode.UnitInsufficientLevel => new UnitInsufficientLevelException(exception),
                (int)ServerErrorCode.DuplicateEntry => new DuplicateEntryException(exception),
                (int)ServerErrorCode.InvalidParameter => new InvalidParameterException(exception),
                (int)ServerErrorCode.NoLotteryResult => new NoLotteryResultException(exception),
                (int)ServerErrorCode.ChangeNameCoolTime => new ChangeNameCoolTimeException(exception),
                (int)ServerErrorCode.NgWord => new NgWordException(exception),
                (int)ServerErrorCode.PlayerNameOverByte => new PlayerNameOverByteException(exception),
                (int)ServerErrorCode.PlayerNameSpaceFirst => new PlayerNameSpaceFirstException(exception),
                (int)ServerErrorCode.AvatarNotFound => new AvatarNotFoundException(exception),
                (int)ServerErrorCode.InvalidPlatform => new InvalidPlatformException(exception),
                (int)ServerErrorCode.UserBirthdateAlreadyRegistered => new UserBirthdateAlreadyRegisteredException(exception),
                (int)ServerErrorCode.UserBirthdateNotRegistered => new UserBirthdateNotRegisteredException(exception),
                (int)ServerErrorCode.DeviceNotFound => new DeviceNotFoundException(exception),
                (int)ServerErrorCode.MultipleDeviceLoginDetected => new MultipleDeviceLoginDetectedException(exception),

                (int)ServerErrorCode.ShopCostTypeNotAd => new ShopCostTypeNotAdException(exception),
                (int)ServerErrorCode.ShopTradeCountLimit => new ShopTradeCountLimitException(exception),
                (int)ServerErrorCode.ShopConditionPackNotReleased => new ShopConditionPackNotReleasedException(exception),
                (int)ServerErrorCode.ShopConditionPackExpired => new ShopConditionPackExpiredException(exception),
                (int)ServerErrorCode.ShopPassNotExpired => new ShopPassNotExpiredException(exception),
                (int)ServerErrorCode.IdleIncentiveCannotReceiveReward => new IdleIncentiveCannotReceiveRewardException(exception),
                (int)ServerErrorCode.IdleIncentiveQuickReceiveCountLimit => new IdleIncentiveQuickReceiveCountLimitException(exception),

                (int)ServerErrorCode.StageNotStart => new StageNotStartException(exception),
                (int)ServerErrorCode.StageNotFound => new StageNotFoundException(exception),
                (int)ServerErrorCode.StageCannotStart => new StageCanNotStartException(exception),
                (int)ServerErrorCode.StageCannotReceiveTreasure => new StageCannotReceiveTreasureException(exception),
                (int)ServerErrorCode.StageContinueLimit => new StageContinueLimitException(exception),
                (int)ServerErrorCode.QuestPeriodOutside => new QuestPeriodOutsideException(exception),
                (int)ServerErrorCode.EventPeriodOutside => new EventPeriodOutsideException(exception),
                (int)ServerErrorCode.StageEventPeriodOutside => new StageEventPeriodOutsideException(exception),
                (int)ServerErrorCode.QuestTypeNotFound => new QuestTypeNotFoundException(exception),
                (int)ServerErrorCode.StageEventPeriodDuplicate => new StageEventPeriodDuplicateException(exception),
                (int)ServerErrorCode.StageEventPartyViolationRule => new StageEventPartyViolationRuleException(exception),
                (int)ServerErrorCode.StageCanNotContinue => new StageCanNotContinueException(exception),

                (int)ServerErrorCode.ItemNotOwned => new ItemAmountIsNotEnoughException(exception),
                (int)ServerErrorCode.ItemAmountIsNotEnough => new ItemAmountIsNotEnoughException(exception),
                (int)ServerErrorCode.ItemTradeAmountLimitExceeded => new ItemTradeAmountLimitExceededException(exception),

                (int)ServerErrorCode.GachaCannotAdLimitDraw => new GachaCannotAdLimitDrawException(exception),
                (int)ServerErrorCode.GachaCannotAdIntervalDraw => new GachaCannotAdIntervalDrawException(exception),
                (int)ServerErrorCode.GachaNotExpectedResourceType => new GachaNotExpectedResourceTypeException(exception),
                (int)ServerErrorCode.GachaBoxIsEmpty => new GachaBoxIsEmptyException(exception),
                (int)ServerErrorCode.GachaTypeUnexpected => new GachaTypeUnexpectedException(exception),
                (int)ServerErrorCode.GachaUnjustCosts => new GachaUnjustCostsException(exception),
                (int)ServerErrorCode.GachaPlayLimit => new GachaPlayLimitException(exception),
                (int)ServerErrorCode.GachaDrewCountDifferent => new GachaDrewCountDifferentException(exception),
                (int)ServerErrorCode.GachaNotExpectedCost => new GachaNotExpectedCostException(exception),
                (int)ServerErrorCode.GachaNotExpectedPlayNum => new GachaNotExpectedPlayNumException(exception),
                (int)ServerErrorCode.GachaExpired => new GachaExpiredException(exception),

                (int)ServerErrorCode.UserBuyStaminaCountLimit => new UserBuyStaminaCountLimitException(exception),
                (int)ServerErrorCode.UserBuyStaminaAdDuringInterval => new UserBuyStaminaAdDuringIntervalException(exception),
                (int)ServerErrorCode.UserStaminaFull => new UserStaminaFullException(exception),
                (int)ServerErrorCode.UserStaminaExceedsLimit => new UserStaminaExceedsLimitException(exception),
                (int)ServerErrorCode.EmblemNotOwned => new EmblemNotOwnedException(exception),
                (int)ServerErrorCode.UserBnidAccessTokenApiError => new UserBnidAccessTokenApiErrorException(exception),
                (int)ServerErrorCode.UserBnidNotLinked => new UserBnidNotLinkedException(exception),
                (int)ServerErrorCode.UserBnidLinkLimit => new UserBnidLinkLimitException(exception),
                (int)ServerErrorCode.UserBnidLinkedOtherUser => new UserBnidLinkedOtherUserException(exception),
                (int)ServerErrorCode.UserBnidLinkLimitMyAccount => new UserBnidLinkLimitMyAccountException(exception),

                (int)ServerErrorCode.UnitAlreadyOwned => new UnitAlreadyOwnedException(exception),
                (int)ServerErrorCode.UnitCannotResetLevel => new UnitCannotResetLevelException(exception),
                (int)ServerErrorCode.UnitLevelUpInvalidLevel => new UnitLevelUpInvalidLevelException(exception),
                (int)ServerErrorCode.UnitLevelUpExceedLimitLevel => new UnitLevelUpExceedLimitLevelException(exception),

                (int)ServerErrorCode.ShopCoinProductIsNotFree => new ShopCoinProductIsNotFreeException(exception),
                (int)ServerErrorCode.ShopBuyCoinCountLimit => new ShopBuyCoinCountLimitException(exception),

                (int)ServerErrorCode.PartyInvalidUnitCount => new PartyInvalidUnitCountException(exception),
                (int)ServerErrorCode.PartyInvalidPartyNo => new PartyInvalidPartyNoException(exception),
                (int)ServerErrorCode.PartyDuplicateUnitId => new PartyDuplicateUnitIdException(exception),
                (int)ServerErrorCode.PartyInvalidUnitId => new PartyInvalidUnitIdException(exception),
                (int)ServerErrorCode.PartyInvalidPartyName => new PartyInvalidPartyNameException(exception),

                (int)ServerErrorCode.MissionCannotReceiveReward => new MissionCannotReceiveRewardException(exception),
                (int)ServerErrorCode.MissionCannotClear => new MissionCannotClearException(exception),
                (int)ServerErrorCode.MissionCannotReceiveOutPeriodEvent => new MissionCannotReceiveOutPeriodEventException(exception),
                (int)ServerErrorCode.MissionCannotReceiveOutPeriodLimitedTerm => new MissionCannotReceiveOutPeriodLimitedTermException(exception),

                (int)ServerErrorCode.ArtworkNotOwned => new ArtworkNotOwnedException(exception),
                (int)ServerErrorCode.OutpostNotOwned => new OutpostNotOwnedException(exception),

                (int)ServerErrorCode.EncyclopediaNotReachedEncyclopediaRank => new EncyclopediaNotReachedEncyclopediaRankException(exception),
                (int)ServerErrorCode.EncyclopediaRewardReceived => new EncyclopediaRewardReceivedException(exception),

                (int)ServerErrorCode.BillingAllowanceFailed => new BillingAllowanceFailedException(exception),
                (int)ServerErrorCode.BillingVerifyReceiptFailed => new BillingVerifyReceiptFailedException(exception),
                (int)ServerErrorCode.BillingVerifyReceiptInvalidReceipt => new BillingVerifyReceiptInvalidReceiptException(exception),
                (int)ServerErrorCode.BillingVerifyReceiptDuplicateReceipt => new IAPServerDuplicatePurchaseException(exception),

                (int)ServerErrorCode.BillingShopInfoNotFound => new BillingShopInfoNotFoundException(exception),
                (int)ServerErrorCode.BillingInvalidEnvironment => new BillingInvalidEnvironmentException(exception),
                (int)ServerErrorCode.BillingUnsupportedBillingPlatform => new BillingUnsupportedBillingPlatformException(exception),
                (int)ServerErrorCode.BillingInvalidAllowance => new BillingInvalidAllowanceException(exception),
                (int)ServerErrorCode.BillingAllowanceAndOprProductNotMatch => new BillingAllowanceAndOprProductNotMatchException(exception),
                (int)ServerErrorCode.BillingAllowanceAndMstStoreProductNotMatch => new BillingAllowanceAndMstStoreProductNotMatchException(exception),
                (int)ServerErrorCode.BillingAppstoreResponseStatusNotOk => new BillingAppstoreResponseStatusNotOkException(exception),
                (int)ServerErrorCode.BillingAppstoreBundleIdNotMatch => new BillingAppstoreBundleIdNotMatchException(exception),
                (int)ServerErrorCode.BillingAppstoreBundleIdNotSet => new BillingAppstoreBundleIdNotSetException(exception),
                (int)ServerErrorCode.BillingGoogleplayReceiptStatusCanceled => new BillingGoogleplayReceiptStatusCanceledException(exception),
                (int)ServerErrorCode.BillingGoogleplayReceiptStatusPending => new BillingGoogleplayReceiptStatusPendingException(exception),
                (int)ServerErrorCode.BillingGoogleplayReceiptStatusOther => new BillingGoogleplayReceiptStatusOtherException(exception),
                (int)ServerErrorCode.BillingUnderagePurchaseLimitExceeded => new BillingUnderagePurchaseLimitExceededException(exception),
                (int)ServerErrorCode.BillingTransactionEndPurchaseLimit => new IAPServerBillingTransactionEndPurchaseLimitException(exception, exception.ServerErrorCode),
                (int)ServerErrorCode.BillingTransactionEnd => new IAPServerBillingTransactionEndException(exception, exception.ServerErrorCode),
                (int)ServerErrorCode.BillingUnknownError => new BillingUnknownErrorException(exception),

                (int)ServerErrorCode.CurrencyNotEnoughPaidCurrency => new CurrencyNotEnoughPaidCurrencyException(exception),
                (int)ServerErrorCode.CurrencyNotEnoughCurrency => new CurrencyNotEnoughCurrencyException(exception),
                (int)ServerErrorCode.CurrencyNotEnoughCash => new CurrencyNotEnoughCashException(exception),
                (int)ServerErrorCode.CurrencyNotFoundFreeCurrency => new CurrencyNotFoundFreeCurrencyException(exception),
                (int)ServerErrorCode.CurrencyNotFoundCurrencySummary => new CurrencyNotFoundCurrencySummaryException(exception),
                (int)ServerErrorCode.CurrencyFailedToAddPaidCurrencyByZero => new CurrencyFailedToAddPaidCurrencyByZeroException(exception),
                (int)ServerErrorCode.CurrencyAddCurrencyByOverMax => new CurrencyAddCurrencyByOverMaxException(exception),
                (int)ServerErrorCode.CurrencyInvalidDebugEnvironment => new CurrencyInvalidDebugEnvironmentException(exception),
                (int)ServerErrorCode.CurrencyUnknownBillingPlatform => new CurrencyUnknownBillingPlatformException(exception),
                (int)ServerErrorCode.CurrencyAddFreeCurrencyByOverMax => new CurrencyAddFreeCurrencyByOverMaxException(exception),
                (int)ServerErrorCode.CurrencyAddPaidCurrencyByOverMax => new CurrencyAddPaidCurrencyByOverMaxException(exception),
                (int)ServerErrorCode.CurrencyUnknownError => new CurrencyUnknownErrorException(exception),

                (int)ServerErrorCode.FailureUpdateByMessageOpenedAt => new FailureUpdateByMessageOpenedAtException(exception),
                (int)ServerErrorCode.UsrMessageNotFound => new UsrMessageNotFoundException(exception),
                (int)ServerErrorCode.ExpiredMessageResource => new ExpiredMessageResourceException(exception),
                (int)ServerErrorCode.ErrorReceivedMessageResource => new ErrorReceivedMessageResourceException(exception),
                (int)ServerErrorCode.FailureUpdateByUserMessages => new FailureUpdateByUserMessagesException(exception),
                (int)ServerErrorCode.MessageRewardByOverMax => new MessageRewardByOverMaxException(exception),


                (int)ServerErrorCode.AdventBattlePeriodOutside => new AdventBattlePeriodOutsideException(exception),
                (int)ServerErrorCode.AdventBattleCannotStart => new AdventBattleCannotStartException(exception),
                (int)ServerErrorCode.AdventBattleSessionMismatch => new AdventBattleSessionMismatchException(exception),
                (int)ServerErrorCode.AdventBattleTypeNotFound => new AdventBattleTypeNotFoundException(exception),
                (int)ServerErrorCode.AdventBattleRewardCategoryNotFound => new AdventBattleRewardCategoryNotFoundException(exception),
                (int)ServerErrorCode.AdventBattleRankingOutPeriod => new AdventBattleRankingOutPeriodException(exception),

                (int)ServerErrorCode.PvpPeriodOutside => new PvpPeriodOutsideException(exception),
                (int)ServerErrorCode.ContentMaintenanceSessionCleanupFailed => new ContentMaintenanceSessionCleanupFailedException(exception),

                (int)ServerErrorCode.AdminDebugFailed => new AdminDebugFailedException(exception),

                (int)ServerErrorCode.RequireClientVersionUpdate => new RequireClientVersionUpdateException(exception),
                (int)ServerErrorCode.RequireResourceUpdate => new RequireResourceUpdateException(exception),
                (int)ServerErrorCode.AvailableAssetVersionNotFound => new AvailableAssetVersionNotFoundException(exception),
                (int)ServerErrorCode.CrossDay => new CrossDayException(exception),
                (int)ServerErrorCode.UserAccountBanTemporaryByCheating => new UserAccountSuspendedException(exception),
                (int)ServerErrorCode.UserAccountBanTemporaryByDetectedAnomaly => new UserAccountBanTemporaryException(exception),
                (int)ServerErrorCode.UserAccountBanPermanent => new UserAccountBanPermanentException(exception),
                (int)ServerErrorCode.UserAccountDeleted => new UserAccountDeletedException(exception),
                (int)ServerErrorCode.UserAccountRefunding => new UserAccountRefundingException(exception),

                (int)ServerErrorCode.Maintenance => new MaintenanceException(exception),
                (int)ServerErrorCode.ContentMaintenance => new ContentMaintenanceException(exception),
                (int)ServerErrorCode.ContentMaintenanceNeedCleanup => new ContentMaintenanceNeedCleanupException(exception),
                (int)ServerErrorCode.MasterDatabaseConnectionsDifferent => new MasterDatabaseConnectionsDifferentException(exception),
                (int)ServerErrorCode.ContentMaintenanceOutside => new ContentMaintenanceOutsideException(exception),

                (int)ServerErrorCode.ExchangeLineupMismatch => new ExchangeLineupMismatch(exception),
                (int)ServerErrorCode.ExchangeLineupTradeLimitExceeded => new ExchangeLineupTradeLimitExceeded(exception),
                (int)ServerErrorCode.ExchangeNotTradePeriod => new ExchangeNotTradePeriod(exception),
                
                (int)ServerErrorCode.BoxGachaCostNotEnough => new BoxGachaCostNotEnoughException(exception),
                (int)ServerErrorCode.BoxGachaDrawCountExceeded => new BoxGachaDrawCountExceededException(exception),
                (int)ServerErrorCode.BoxGachaPeriodOutside => new BoxGachaPeriodOutsideException(exception),
                (int)ServerErrorCode.BoxGachaStockNotEnough => new BoxGachaStockNotEnoughException(exception),

                _ => exception,
            };
        }
    }
}

