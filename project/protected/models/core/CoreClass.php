<?php
    class BookSeatingCity
    {
        const cityCode = "sCityCode";
        const cityID = "sCityID";
        const cityName ="sCityName";
        const interfaceID = "iInterfaceID";
    }
    
    class BookSeatingCinema
    {
        const cinemaId = "sCinemaInterfaceNo";
        const cinemaName = "sCinemaName";
        const cinemaTerminal ="sTerminalInterfaceNo";
        const endBuyTime ="iEndBuyTime";
        const epiaoCinemaId = "iCinemaID";
        const hallCount = "iHallNum";
        const interfaceId = "iInterfaceID";
        const interfaceCinemaKey = "InterfaceCinemaKey";
        const hallInfo = 'hallInfo';
    }
    
    class BookSeatingRoom
    {
        const  cinemaId = "sCinemaInterfaceNo";
        const  ePiaoCinemaId = "iCinemaID";
        const  interfaceId = "iInterfaceID";
        const  key = "InterfaceRoomKey";
        const  roomId ="sRoomInterfaceNo";
        const  roomName="sRoomName";
        const  seatCount ="iSeatNum";
        const  seatInfo ="sSeatInfo";
        const  sectionId ="sSectionId";
        const  sourceId ="sSourceId";
        const  isIMax = "bIsIMAX";
        const  isVip = 'isVip';
    }
    
    class BookSeatingSeat
    {
        const  columnId = "ColumnId";
        const  columnName = "ColumnName";
        const  columnNum = "ColumnNum";
        const  loveInd = "LoveInd";
        const  relSeat = "RelSeat";
        const  rowId = "RowId";
        const  rowName = "RowName";
        const  rowNum = "RowNum";
        const  seatId = "SeatId";
        const  seatIndex = "SeatIndex";     //为网票网添加这个字段
        const  seatStatus = "SeatStatus";
        const  sectionId = "SectionId";
        const  sourceId= "SourceId";
    }
    
    class EPWArrangePrice
    {
        const   price = "mPrice";
        const   settlePrice = "mSettlementPrice";
        const   standardPrice = "mCinemaPrice";
        const   VIPPrice = "VIPPrice";
    }
    
    class InterfaceArrangePrice
    {
        //标准价格--接口方正价出售价格
        const standardPrice = "StandardPrice";
        //实际价格--接口方实际出售价格
        const cinemaPrice = "CinemaPrice";
        //接口方与EPW结算价格
        const settlePrice = "SettlePrice";
        //接口方服务费
        const feePrice = "FeePrice";
    }
    
    class BookSeatingFilm
    {
        const   cnName = "sMovieName";
        const   director = "director";
        const   distributor = "distributor";
        const   enName = "enName";
        const   favorable = "favorable";
        const   Id = "sMovieNo";
        const   imageUrl = "imageUrl";
        const   interfaceId = "iInterfaceId";
        const   longTime = "longTime";
        const   released = "released";
        const   starring = "starring";
        const   synopsis = "price";
    }
    
    class ArrangeKey
    {
        const cinemaId = "CinemaId";
        const playTime = "PlayTime";
        const roomId = "RoomId";
    }
    
    class BookSeatingArrange
    {
        const arrangeId  = "sRoomMovieInterfaceNo";
        const arrangePrice  = "ArrangePrice";
        const baseCouponNum  = "iCouponNum";
        const cinemaId  = "sCinemaInterfaceNo";
        const dimensional  = "sDimensional";
        const endTime  = "dEndBuyDate";
        const epiaoCinemaId = "iEpiaoCinemaID";
        const epiaoCinemaName = 'sCinemaName';
        const epiaoRoomId  = "iRoomID";
        const epiaoRoomName ='sRoomName';
        const fee  = "mFee";
        const film  = "Film";
        const hallId = "sRoomInterfaceNo";
        const hallName = "HallName";
        const IMax = "sIMax";
        const interfaceId = "iInterfaceID";
        const key = "Key";
        const language = "sLanguage";
        const screenTime = "dBeginTime";
        const updateLevel= "UpdateLevel";
        const updateType = "UpdateType";
        const dataSource = "DataSource";
        const movieId = 'iMovieID';
        const movieName ='sMovieName';
        const huoDongPrice ='huodongPrice';
        const sign = 'md5sign';
    }
    
    class BookSeatingCreateOrderResult extends BookSeatingCommonResult
    {
         const interfaceOrderNo = "InterfaceOrderNo";
    }
    
    class BookSeatingPayOrderResult  extends BookSeatingCommonResult
    {
        const interfaceValidCode = "InterfaceValidCode";
        const interfaceVerificationCode = "InterfaceVerificationCode";
    }
    
     class OrderSeatInfo
    {
        const  order_seatId = "order_seatId";
        const  outerOrderId = "outerOrderId";
        const  sCinemaInterfaceNo = "sCinemaInterfaceNo";
        const  sRoomInterfaceNo = "sRoomInterfaceNo";
        const  sSeatInfo = "sSeatInfo"; //
        const  mFee = "mFee";
        const  sInterfaceOrderNo = "sInterfaceOrderNo";
        const  sInterfaceValidCode = "sInterfaceValidCode";
        const  dCreateTime  = "dCreateTime";
        const  sCinemaName  = "sCinemaName";
        const  iRoomID  = "iRoomID";
        const  sRoomName  = "sRoomName";
        const  sMovieName  = "sMovieName";
        const  roomMovieNo  = "iRoomMovieID";
        const  sMovieID  = "iMovieID";
        const  iCinemaId = "iCinemaID";
        const  sRoomMovieInterfaceNo  = "sRoomMovieInterfaceNo";
        const  iInterfaceID  = "iInterfaceID";
        const  sPhone  = "sPhone";
        const  IMax = "sIMax";
        const  sDimensional='sDimensional';
        const  sLanguage = 'sLanguage';
        const  iRoommovieID = 'iRoomMovieID';
        const  sMovieInterfaceNo = 'sMovieInterfaceNo';
        const  userId = 'iUserId';
        const  price  = 'mPrice';
        const  status='status';
        const  dPlayTime='dPlayTime';
        const mSettingPrice ='mSettingPrice';
        const lowValue ='lowValue';
    }
    
    class BookSeatingCommonResult
    {
        const resultCode = "ResultCode";
        const resultMessage = "ResultMessage";
    }
    
    class BookSeatingLockSeat
    {
        const  columnId = "ColumnId";
        const  rowId = "RowId";
        const  seatId = "SeatId";
        const  seatStatus = "SeatStatus";
        const  sectionId = "SectionId";
    }
    
    class BookSeatingOrder
    {
        const fetchNo = "FetchNo";
        const interfaceId = "InterfaceId";
        const orderDesc = "OrderDesc";
        const orderId = "OrderId";
        const orderStatus = "OrderStatus";
        const playTime = "PlayTime";
    }
    
    class Orders
    {
        const orderId = "orderId";
        const outerOrderId = "outerOrderId";
        const orderStatus = "orderStatus";
        const refundStatus = "refundStatus";
        const mPrice = "mPrice";
        const totalPrice = "totalPrice";
        const orderInfo = "orderInfo";
        const sendPhone = "sendPhone";
        const iUserID = "iUserId";
        const fromClient = "fromClient";
        const orderType = "orderType";
        const orderPayType = "orderPayType";
        const createTime = "createTime";
        const refundTime = "refundTime";
        const closeTime = "closeTime";
        const returnUrl = "returnUrl";
        const massageId = "massageId";
        const iownSeats ='iownSeats';
        const iHuoDongItemID ='iHuoDongItemID';
        const huodong_types ='huodong_types';

    }
    
    
    class PaylogInfo
    {
        const payLogId = "payLogId";
        const outerOrderId = "outerOrderId";
        const createTime = "createTime";
        const totalPrice = "totalPrice";
        const bankType = "bankType";
        const payTime = "payTime";
        const epiaotradeId="tradeId";
        const sCheckNo = "sCheckNo";
        const sPassword="sPassword";
        const iCouponID = "iCouponID";
        const ticketCode = "ticketCode";
        const count = "count";
        const tradeNo = "tradeNo";
        const status = "status";  
        const iHuodongItemID="iHuodongItemID";
        const iUserID="iUserID";
        const sVoucherPassWord="sVoucherPassWord"; 
        const iCardCount ='cardCount';
    }
    
    
    
   


    
    


